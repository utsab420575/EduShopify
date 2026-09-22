<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\AwardService;
use App\Services\QuotationDecisionService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the three follow-up features built on top of the quotation_items
 * (Product Response) / quotation_item_offers (individual offers) design:
 * 1) revision snapshots freezing every offer (not just the primary),
 * 2) the buyer comparison service exposing offers nested under each
 *    Product Response, and
 * 3) the buyer selecting a specific offer, which then drives the award/PO.
 */
class QuotationRevisionAndOfferSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeActiveAccount(string $capability, string $email): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability' => $capability,
            'name' => ucfirst($capability).' Revision Test User',
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

        if ($capability === 'supplier') {
            $plan = SubscriptionPlan::create([
                'name' => 'Free Plan', 'slug' => 'free-plan-revision-'.uniqid(), 'billing_type' => 'free',
                'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
                'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
            ]);
            $subscription = Subscription::create([
                'supplier_account_id' => $account->id, 'plan_id' => $plan->id, 'selected_by_user_id' => $user->id,
                'provider' => 'free', 'status' => 'pending',
            ]);
            $subscription->activate();
        }

        return $user->fresh();
    }

    private function makeOpenRfq(User $buyer): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Revision RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'allow_alternative_products' => true,
            'items' => [
                ['item_type' => 'product', 'item_name' => 'I need computers', 'quantity' => 10],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    private function makeListing(User $supplier, string $suffix): Listing
    {
        $category = Category::create([
            'name' => 'Electronics'.$suffix, 'slug' => 'electronics-revision-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        return Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-REVISION-'.uniqid(),
            'listing_type' => 'product',
            'name' => 'Dell Model X'.$suffix,
            'slug' => 'dell-model-x-revision-'.uniqid(),
            'main_category_id' => $category->id,
            'base_price' => 700,
            'currency_code' => 'USD',
            'pricing_type' => 'fixed',
            'setup_step' => 4,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_submitting_a_quotation_snapshots_every_offer_including_alternatives_and_documents(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'revision-s1@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'revision-b1@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
                ],
            ]],
        ]);

        // Attach a document to the alternative offer before submitting, to
        // confirm media gets physically copied into the revision snapshot.
        $altOffer = $quotation->items->first()->offers()->where('is_primary', false)->first();
        $altOffer->addMediaFromString('fake pdf bytes')->usingFileName('alt-quote.pdf')->toMediaCollection('document');

        $quotation = $service->submitDraft($quotation);

        $revision = $quotation->revisions()->where('revision_no', 1)->firstOrFail();
        $revisionItem = $revision->items()->firstOrFail();

        // Both offers survived into the revision, not just the primary.
        $this->assertCount(2, $revisionItem->offers);
        $revisionAlt = $revisionItem->offers()->where('is_primary', false)->first();
        $this->assertEquals('HP Model X', $revisionAlt->product_name);
        $this->assertEquals($altOffer->id, $revisionAlt->source_offer_id);

        // The document was physically copied, not just referenced.
        $this->assertCount(1, $revisionAlt->getMedia('document'));
        $this->assertNotEquals(
            $altOffer->getFirstMedia('document')->id,
            $revisionAlt->getFirstMedia('document')->id
        );

        // Deleting the live alternative offer must not affect the frozen revision.
        $altOffer->delete();
        $this->assertCount(2, $revisionItem->fresh()->offers);
    }

    public function test_comparison_service_nests_offers_under_each_product_response(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'revision-s2@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'revision-b2@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
                    ['offer_method' => 'custom', 'product_name' => 'Lenovo Model X', 'quantity' => 10, 'unit_price' => 850, 'is_primary' => false],
                ],
            ]],
        ]);
        $quotation = $service->submitDraft($quotation);

        $comparison = app(\App\Services\QuotationComparisonService::class);
        $resolved = $comparison->resolve($rfq, [$quotation->id]);
        $itemComparison = $comparison->buildItemComparison($rfq, $resolved['quotations']);

        $productResponses = $itemComparison[0]['offers'][$quotation->id];
        $this->assertCount(1, $productResponses); // one Product Response
        $offers = $productResponses[0]['offers'];
        $this->assertCount(3, $offers); // three nested offers
        $this->assertEqualsCanonicalizing(
            ['Dell Model X', 'HP Model X', 'Lenovo Model X'],
            array_column($offers, 'product_name')
        );
    }

    public function test_buyer_can_select_a_specific_offer_and_it_drives_the_award_and_purchase_order(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'revision-s3@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'revision-b3@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;
        $listing = $this->makeListing($supplier, 'Sel');

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing->id, 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'HP Model X (better spec)', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
                ],
            ]],
        ]);
        $quotation = $service->submitDraft($quotation);

        $item = $quotation->items->first();
        $hpOffer = $item->offers()->where('is_primary', false)->first();

        // Buyer prefers the HP alternative over the supplier's own primary (Dell).
        $response = $this->actingAs($buyer)->post(
            route('buyer.quotations.items.offers.select', [$quotation, $item, $hpOffer])
        );
        $response->assertRedirect();

        $this->assertTrue((bool) $hpOffer->fresh()->is_selected);
        $this->assertFalse((bool) $item->offers()->where('is_primary', true)->first()->is_selected);

        // Award and accept — the PO must reflect the buyer's selected HP
        // offer (900 * 10 = 9000), not the supplier's primary Dell offer (7000).
        $award = app(AwardService::class)->create($quotation, $buyer);
        app(AwardResponseService::class)->accept($award);

        $po = \App\Models\PurchaseOrder::where('award_id', $award->id)->firstOrFail();
        $this->assertEquals('9000.00', $po->grand_total);
        $this->assertCount(1, $po->items);
        $poItem = $po->items->first();
        $this->assertEquals('HP Model X (better spec)', $poItem->item_name);
        $this->assertEquals($hpOffer->id, $poItem->quotation_item_offer_id);
    }

    public function test_award_falls_back_to_the_primary_offer_when_the_buyer_never_selected_one(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'revision-s4@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'revision-b4@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
                ],
            ]],
        ]);
        $quotation = $service->submitDraft($quotation);

        // No selectOffer call at all this time.
        $award = app(AwardService::class)->create($quotation, $buyer);
        app(AwardResponseService::class)->accept($award);

        $po = \App\Models\PurchaseOrder::where('award_id', $award->id)->firstOrFail();
        $this->assertEquals('7000.00', $po->grand_total);
        $this->assertEquals('Dell Model X', $po->items->first()->item_name);
    }

    public function test_a_different_buyer_cannot_select_an_offer_on_someone_elses_quotation(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'revision-s5@example.com');
        $buyerA = $this->makeActiveAccount('buyer', 'revision-bA@example.com');
        $buyerB = $this->makeActiveAccount('buyer', 'revision-bB@example.com');
        $rfq = $this->makeOpenRfq($buyerA);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
                ],
            ]],
        ]);
        $quotation = $service->submitDraft($quotation);
        $item = $quotation->items->first();
        $hpOffer = $item->offers()->where('is_primary', false)->first();

        $response = $this->actingAs($buyerB)->post(
            route('buyer.quotations.items.offers.select', [$quotation, $item, $hpOffer])
        );

        $response->assertForbidden();
        $this->assertFalse((bool) $hpOffer->fresh()->is_selected);
    }
}
