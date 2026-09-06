<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\Catalog\ProductComparisonService;
use App\Services\ReviewModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Phase 1 of the dual review system: reviews.listing_id / review_type,
 * listings.product_rating / product_reviews_count, and the aggregation +
 * display built on top. No buyer-facing "write a product review" flow yet
 * (deliberately deferred) — these tests create Review rows directly, the
 * same way the pre-existing supplier-rating test in AdminDashboardTest does.
 */
class DualReviewSystemTest extends TestCase
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
            'name' => 'Dual Review Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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
            'name' => ucfirst($capability) . ' Dual Review Test User',
            'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999),
            'password' => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->{$capability . 'Capability'}()->update(['status' => 'active']);
        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    private function makeListing(User $supplier, string $suffix = ''): Listing
    {
        $category = Category::create([
            'name' => 'Electronics' . $suffix, 'slug' => 'electronics-dual-review-' . uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        return Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-DUAL-' . uniqid(),
            'listing_type' => 'product',
            'name' => 'Test Widget' . $suffix,
            'slug' => 'test-widget-dual-review-' . uniqid(),
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

    public function test_publishing_a_product_review_recalculates_the_listings_product_rating_not_the_suppliers(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-publish@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier1@example.com');
        $listing = $this->makeListing($supplier);

        $review = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'listing_id' => $listing->id,
            'review_type' => 'product',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'purchase_experience',
            'rating' => 5,
            'status' => 'pending',
        ]);

        app(ReviewModerationService::class)->publish($review, $admin);

        $this->assertSame('published', $review->fresh()->status);
        $this->assertEquals(5.0, (float) $listing->fresh()->product_rating);
        $this->assertSame(1, $listing->fresh()->product_reviews_count);

        // The supplier's own aggregate must be untouched by a product review.
        $supplier->account->supplierProfile->refresh();
        $this->assertEquals(0.0, (float) $supplier->account->supplierProfile->rating);
        $this->assertSame(0, $supplier->account->supplierProfile->reviews_count);
    }

    public function test_publishing_a_supplier_review_still_recalculates_only_the_supplier_not_any_listing(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-supplier-only@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier2@example.com');
        $listing = $this->makeListing($supplier);

        $review = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'review_type' => 'supplier',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'quotation_experience',
            'rating' => 4,
            'status' => 'pending',
        ]);

        app(ReviewModerationService::class)->publish($review, $admin);

        $supplier->account->supplierProfile->refresh();
        $this->assertEquals(4.0, (float) $supplier->account->supplierProfile->rating);
        $this->assertSame(1, $supplier->account->supplierProfile->reviews_count);
        $this->assertEquals(0.0, (float) $listing->fresh()->product_rating);
        $this->assertSame(0, $listing->fresh()->product_reviews_count);
    }

    public function test_hiding_a_published_product_review_recalculates_the_listing_back_down(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-hide@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier3@example.com');
        $listing = $this->makeListing($supplier);

        $review = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'listing_id' => $listing->id,
            'review_type' => 'product',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'purchase_experience',
            'rating' => 3,
            'status' => 'pending',
        ]);

        $service = app(ReviewModerationService::class);
        $service->publish($review, $admin);
        $this->assertSame(1, $listing->fresh()->product_reviews_count);

        $service->hide($review->fresh(), $admin, 'Inappropriate content');

        $this->assertSame(0, $listing->fresh()->product_reviews_count);
        $this->assertEquals(0.0, (float) $listing->fresh()->product_rating);
    }

    public function test_product_comparison_headers_expose_product_rating_alongside_supplier_rating(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-compare@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer4@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier4@example.com');
        $listing = $this->makeListing($supplier);

        $service = app(ProductComparisonService::class);
        $resolved = $service->resolve([['listing_id' => $listing->id, 'variant_id' => null]]);
        $headers = $service->buildHeaders($resolved['pairs']);

        $this->assertSame(0.0, $headers[0]['product_rating']);
        $this->assertSame(0, $headers[0]['product_reviews_count']);

        $review = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'listing_id' => $listing->id,
            'review_type' => 'product',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'purchase_experience',
            'rating' => 5,
            'status' => 'pending',
        ]);
        app(ReviewModerationService::class)->publish($review, $admin);

        $resolved = $service->resolve([['listing_id' => $listing->id, 'variant_id' => null]]);
        $headers = $service->buildHeaders($resolved['pairs']);

        $this->assertSame(5.0, $headers[0]['product_rating']);
        $this->assertSame(1, $headers[0]['product_reviews_count']);
    }

    public function test_the_public_listing_page_renders_both_rating_summaries(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-page@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer5@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier5@example.com');
        $listing = $this->makeListing($supplier);

        $productReview = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'listing_id' => $listing->id,
            'review_type' => 'product',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'purchase_experience',
            'rating' => 5,
            'status' => 'pending',
        ]);
        $supplierReview = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'review_type' => 'supplier',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'quotation_experience',
            'rating' => 3,
            'status' => 'pending',
        ]);
        $moderation = app(ReviewModerationService::class);
        $moderation->publish($productReview, $admin);
        $moderation->publish($supplierReview, $admin);

        $response = $this->get(route('frontend.listings.show', $listing));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('Product Rating', $html);
        $this->assertStringContainsString('Supplier Rating', $html);
        $this->assertStringContainsString('5.0', $html); // product rating
        $this->assertStringContainsString('3.0', $html); // supplier rating
    }

    public function test_admin_review_detail_labels_a_product_review_distinctly_from_a_supplier_review(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('dual-review-admin-detail@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'dual-review-buyer6@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'dual-review-supplier6@example.com');
        $listing = $this->makeListing($supplier, ' Special');

        $review = Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'listing_id' => $listing->id,
            'review_type' => 'product',
            'created_by_user_id' => $buyer->id,
            'review_context' => 'purchase_experience',
            'rating' => 4,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews.show', $review));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('Product Review', $html);
        $this->assertStringContainsString('Test Widget Special', $html);
    }
}
