<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Review;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\AwardService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The hybrid review write flow: one buyer submission for a whole quotation
 * or purchase order fans out into 1 review_type=supplier row (as before)
 * plus 1 review_type=product row per distinct listing on that order, all
 * sharing the initial rating/comment. The buyer can then edit any one
 * product row independently without touching the others.
 */
class HybridReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeAdmin(string $email): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name' => 'Hybrid Review Admin', 'email' => $email, 'phone' => '+1000000'.random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $admin->id, 'member_type' => 'owner',
            'is_primary_owner' => true, 'status' => 'active', 'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    private function makeActiveAccount(string $capability, string $email): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability' => $capability,
            'name' => ucfirst($capability).' Hybrid Review Test User',
            'email' => $email,
            'phone' => '+1555000'.random_int(1000, 9999),
            'password' => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->{$capability.'Capability'}()->update(['status' => 'active']);
        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    private function makeListing(User $supplier, string $suffix): Listing
    {
        $category = Category::create([
            'name' => 'Electronics'.$suffix, 'slug' => 'electronics-hybrid-review-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        return Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-HYBRID-'.uniqid(),
            'listing_type' => 'product',
            'name' => 'Test Widget'.$suffix,
            'slug' => 'test-widget-hybrid-review-'.uniqid(),
            'main_category_id' => $category->id,
            'base_price' => 10,
            'currency_code' => 'USD',
            'pricing_type' => 'fixed',
            'setup_step' => 4,
            'approval_status' => 'approved',
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    private function makeOpenRfq(User $buyer): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Multi-product RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items' => [
                ['item_type' => 'product', 'item_name' => 'Item A', 'quantity' => 10],
                ['item_type' => 'product', 'item_name' => 'Item B', 'quantity' => 5],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    /**
     * Two-item quotation offering two different listings — the exact shape
     * the fan-out logic needs to be exercised against.
     */
    private function makeSubmittedQuotationWithTwoListings(Rfq $rfq, User $supplier, Listing $listingA, Listing $listingB): Quotation
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QT-HYBRID-'.uniqid(),
            'rfq_id' => $rfq->id,
            'supplier_account_id' => $supplier->account->id,
            'submitted_by_user_id' => $supplier->id,
            'rfq_version_no' => $rfq->current_version_no,
            'subtotal' => 1500,
            'grand_total' => 1500,
            'currency_code' => 'USD',
            'lead_time_days' => 14,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'rfq_item_id' => $rfq->items[0]->id,
            'offered_listing_id' => $listingA->id,
            'item_name' => 'Item A',
            'quantity' => 10,
            'unit_price' => 100,
            'line_total' => 1000,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'rfq_item_id' => $rfq->items[1]->id,
            'offered_listing_id' => $listingB->id,
            'item_name' => 'Item B',
            'quantity' => 5,
            'unit_price' => 100,
            'line_total' => 500,
        ]);

        $rfq->increment('quotations_count');

        return $quotation->fresh(['items']);
    }

    public function test_reviewing_a_submitted_quotation_fans_out_into_one_product_review_per_distinct_listing(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'hybrid-buyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier1@example.com');
        $listingA = $this->makeListing($supplier, ' A');
        $listingB = $this->makeListing($supplier, ' B');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), [
            'rating' => 4,
            'title' => 'Great experience',
            'comment' => 'Would order again.',
        ])->assertRedirect();

        $this->assertSame(1, Review::where('quotation_id', $quotation->id)->where('review_type', 'supplier')->count());

        $productReviews = Review::where('quotation_id', $quotation->id)->where('review_type', 'product')->get();
        $this->assertSame(2, $productReviews->count());
        $this->assertEqualsCanonicalizing([$listingA->id, $listingB->id], $productReviews->pluck('listing_id')->all());

        foreach ($productReviews as $review) {
            $this->assertSame(4, $review->rating);
            $this->assertSame('Great experience', $review->title);
            $this->assertSame('pending', $review->status);
            $this->assertSame($buyer->account->id, $review->buyer_account_id);
            $this->assertSame($supplier->account->id, $review->supplier_account_id);
        }
    }

    public function test_reviewing_a_completed_purchase_order_fans_out_via_the_quotation_item_relation(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'hybrid-buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier2@example.com');
        $listingA = $this->makeListing($supplier, ' C');
        $listingB = $this->makeListing($supplier, ' D');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);
        $award = app(AwardService::class)->create($quotation, $buyer);
        $award = app(AwardResponseService::class)->accept($award);
        $po = PurchaseOrder::where('award_id', $award->id)->firstOrFail();

        $this->actingAs($buyer)->post(route('buyer.purchase-orders.complete', $po))->assertRedirect();

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-purchase-order', $po->fresh()), [
            'rating' => 5,
        ])->assertRedirect();

        $this->assertSame(1, Review::where('purchase_order_id', $po->id)->where('review_type', 'supplier')->count());

        $productReviews = Review::where('purchase_order_id', $po->id)->where('review_type', 'product')->get();
        $this->assertSame(2, $productReviews->count());
        $this->assertEqualsCanonicalizing([$listingA->id, $listingB->id], $productReviews->pluck('listing_id')->all());
    }

    public function test_a_buyer_can_edit_one_product_review_independently_without_touching_the_others(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'hybrid-buyer3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier3@example.com');
        $listingA = $this->makeListing($supplier, ' E');
        $listingB = $this->makeListing($supplier, ' F');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), ['rating' => 3]);

        $reviewA = Review::where('quotation_id', $quotation->id)->where('listing_id', $listingA->id)->firstOrFail();
        $reviewB = Review::where('quotation_id', $quotation->id)->where('listing_id', $listingB->id)->firstOrFail();
        $supplierReview = Review::where('quotation_id', $quotation->id)->where('review_type', 'supplier')->firstOrFail();

        $this->actingAs($buyer)->put(route('buyer.reviews.update-product', $reviewA), [
            'rating' => 1,
            'title' => 'Actually disappointing',
            'comment' => 'Changed my mind about this one.',
        ])->assertRedirect();

        $reviewA->refresh();
        $this->assertSame(1, $reviewA->rating);
        $this->assertSame('Actually disappointing', $reviewA->title);
        $this->assertSame('pending', $reviewA->status);

        // Sibling product review and the supplier review are untouched.
        $this->assertSame(3, $reviewB->fresh()->rating);
        $this->assertSame(3, $supplierReview->fresh()->rating);
    }

    /**
     * This app's global Gate::before() (AppServiceProvider) grants any tenant
     * account owner every non-"platform." ability outright, bypassing custom
     * policy logic entirely for owner-role users — the same reason several
     * pre-existing "cannot access another buyer's X" tests are already known
     * failures elsewhere in this suite. ReviewPolicy::updateProductReview()
     * is therefore not the real backstop for an owner; ReviewService's own
     * ownership guard is — so that's what this test verifies actually holds.
     */
    public function test_a_buyer_cannot_edit_another_buyers_product_review(): void
    {
        $this->seedBase();
        $buyerA = $this->makeActiveAccount('buyer', 'hybrid-buyer4a@example.com');
        $buyerB = $this->makeActiveAccount('buyer', 'hybrid-buyer4b@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier4@example.com');
        $listingA = $this->makeListing($supplier, ' G');
        $listingB = $this->makeListing($supplier, ' H');

        $rfq = $this->makeOpenRfq($buyerA);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);
        $this->actingAs($buyerA)->post(route('buyer.reviews.store-for-quotation', $quotation), ['rating' => 4]);

        $review = Review::where('quotation_id', $quotation->id)->where('listing_id', $listingA->id)->firstOrFail();

        $this->actingAs($buyerB)->put(route('buyer.reviews.update-product', $review), ['rating' => 1])
            ->assertRedirect()
            ->assertSessionHasErrors('rating');

        $this->assertSame(4, $review->fresh()->rating);
    }

    public function test_a_buyer_cannot_edit_the_whole_order_supplier_review_via_the_product_update_route(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'hybrid-buyer5@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier5@example.com');
        $listingA = $this->makeListing($supplier, ' I');
        $listingB = $this->makeListing($supplier, ' J');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);
        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), ['rating' => 4]);

        $supplierReview = Review::where('quotation_id', $quotation->id)->where('review_type', 'supplier')->firstOrFail();

        $this->actingAs($buyer)->put(route('buyer.reviews.update-product', $supplierReview), ['rating' => 1])
            ->assertRedirect()
            ->assertSessionHasErrors('rating');

        $this->assertSame(4, $supplierReview->fresh()->rating);
    }

    public function test_submitting_a_second_review_for_the_same_quotation_is_still_blocked_after_the_fan_out(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'hybrid-buyer6@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'hybrid-supplier6@example.com');
        $listingA = $this->makeListing($supplier, ' K');
        $listingB = $this->makeListing($supplier, ' L');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotationWithTwoListings($rfq, $supplier, $listingA, $listingB);

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), ['rating' => 4])
            ->assertRedirect();

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), ['rating' => 2])
            ->assertRedirect()
            ->assertSessionHasErrors('rating');

        $this->assertSame(1, Review::where('quotation_id', $quotation->id)->where('review_type', 'supplier')->count());
        $this->assertSame(2, Review::where('quotation_id', $quotation->id)->where('review_type', 'product')->count());
    }
}
