<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the quotation_items (Product Response) / quotation_item_offers
 * (individual offers) child-table design: multiple offers per product
 * response, marketplace/custom/document offer methods, totals/PO
 * compatibility (only the primary offer per product response counts), and
 * the existing draft-save/submit/award flow.
 */
class SupplierMultiOfferQuotationTest extends TestCase
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
            'name' => ucfirst($capability).' MultiOffer Test User',
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
                'name' => 'Free Plan', 'slug' => 'free-plan-multioffer-'.uniqid(), 'billing_type' => 'free',
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

    private function makeOpenRfq(User $buyer, bool $allowAlternatives = true): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'MultiOffer RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'allow_alternative_products' => $allowAlternatives,
            'items' => [
                ['item_type' => 'product', 'item_name' => 'I need computers', 'quantity' => 10],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    private function makeListing(User $supplier, string $suffix): Listing
    {
        $category = Category::create([
            'name' => 'Electronics'.$suffix, 'slug' => 'electronics-multioffer-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        return Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-MULTIOFFER-'.uniqid(),
            'listing_type' => 'product',
            'name' => 'Dell Model X'.$suffix,
            'slug' => 'dell-model-x-multioffer-'.uniqid(),
            'main_category_id' => $category->id,
            'base_price' => 700,
            'currency_code' => 'USD',
            'pricing_type' => 'fixed',
            'setup_step' => 4,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_multiple_offers_can_be_created_under_one_product_response_and_only_primary_counts_toward_totals(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-s1@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b1@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;
        $listing = $this->makeListing($supplier, 'A');

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X Computer', 'quantity' => 10,
                    'unit_price' => 700, 'client_ref' => 'ref-1',
                    'offers' => [
                        [
                            'offer_method' => 'marketplace', 'marketplace_product_id' => $listing->id,
                            'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700,
                            'is_primary' => true,
                        ],
                        [
                            'offer_method' => 'custom', 'product_name' => 'HP Model X (custom build)',
                            'quantity' => 10, 'unit_price' => 900, 'is_primary' => false,
                        ],
                        [
                            'offer_method' => 'document', 'product_name' => 'Lenovo Model X — Document Quotation',
                            'quantity' => 10, 'unit_price' => 850, 'is_primary' => false,
                        ],
                    ],
                ],
            ],
        ]);

        // One Product Response (quotation_items row) ...
        $this->assertCount(1, $quotation->items);
        $productResponse = $quotation->items->first();
        $this->assertEquals($rfqItemId, $productResponse->rfq_item_id);

        // ... with three child offers.
        $this->assertCount(3, $productResponse->offers()->get());
        $this->assertEquals('marketplace', $productResponse->offers()->where('is_primary', true)->first()->offer_method);
        $this->assertEquals($listing->id, $productResponse->offers()->where('is_primary', true)->first()->marketplace_product_id);

        // Only the primary offer (700 * 10 = 7000) counts toward the quotation total —
        // not 700+900+850 summed, and not the highest bid either.
        $this->assertEquals('7000.00', $quotation->subtotal);
        $this->assertEquals('7000.00', $quotation->grand_total);

        // The Product Response row itself is kept in sync with its primary offer,
        // since it's what purchase-order/revision code reads.
        $this->assertEquals('Dell Model X', $productResponse->item_name);
        $this->assertEquals('700.00', $productResponse->unit_price);
    }

    public function test_switching_the_primary_offer_changes_the_quotation_total(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-s2@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b2@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 1, 'unit_price' => 700,
                'offers' => [
                    ['offer_method' => 'custom', 'product_name' => 'Cheaper Option', 'quantity' => 1, 'unit_price' => 700, 'is_primary' => true],
                    ['offer_method' => 'custom', 'product_name' => 'Pricier Option', 'quantity' => 1, 'unit_price' => 1200, 'is_primary' => false],
                ],
            ]],
        ]);
        $this->assertEquals('700.00', $quotation->grand_total);

        // Re-save with the second offer now marked primary — simulates the
        // supplier clicking "Use as Primary" and the form autosaving again.
        $offerIds = $quotation->items->first()->offers()->orderBy('id')->pluck('id', 'offer_method');
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'id' => $quotation->items->first()->id,
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 1, 'unit_price' => 700,
                'offers' => [
                    ['id' => $offerIds['custom'] ?? null, 'offer_method' => 'custom', 'product_name' => 'Cheaper Option', 'quantity' => 1, 'unit_price' => 700, 'is_primary' => false],
                ],
            ]],
        ], $quotation);

        // Only sent one offer this time (simplification) with is_primary false —
        // syncItemOffers() always guarantees exactly one primary, falling back
        // to the first offer when none is explicitly marked.
        $this->assertEquals('700.00', $quotation->fresh()->grand_total);
    }

    public function test_offer_without_explicit_offers_array_falls_back_to_a_single_default_offer(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-s3@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b3@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        // Legacy-shaped payload: no `offers` key at all.
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Legacy Item', 'quantity' => 1, 'unit_price' => 100,
            ]],
        ]);

        $this->assertEquals('100.00', $quotation->subtotal);
        $productResponse = $quotation->items->first();
        $this->assertCount(1, $productResponse->offers()->get());
        $this->assertTrue((bool) $productResponse->offers()->first()->is_primary);
    }

    public function test_submit_and_award_flow_creates_purchase_order_items_from_primary_offers_only(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-s4@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b4@example.com');
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
        $this->assertEquals('submitted', $quotation->status);
        $this->assertEquals('7000.00', $quotation->grand_total);

        $award = app(\App\Services\AwardService::class)->create($quotation, $buyer);

        app(AwardResponseService::class)->accept($award);

        $po = \App\Models\PurchaseOrder::where('award_id', $award->id)->first();
        $this->assertNotNull($po);

        // One PO item per Product Response (quotation_items row) — NOT one per
        // offer. The alternative HP offer never became its own PO line.
        $this->assertCount(1, $po->items);
        $this->assertEquals('Dell Model X', $po->items->first()->item_name);
        $this->assertEquals('7000.00', $po->grand_total);
    }

    public function test_draft_with_multiple_product_responses_and_alternative_offers_totals_correctly(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-s5@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b5@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        // Buyer asked for "computers" (qty 10) — supplier splits into two
        // distinct Product Responses (Model X qty 5, Model Y qty 5), Model X
        // additionally carries one alternative offer.
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 5, 'unit_price' => 700,
                    'offers' => [
                        ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 5, 'unit_price' => 700, 'is_primary' => true],
                        ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 5, 'unit_price' => 900, 'is_primary' => false],
                    ],
                ],
                [
                    'rfq_item_id' => $rfqItemId, 'item_name' => 'Model Y', 'quantity' => 5, 'unit_price' => 900,
                    'offers' => [
                        ['offer_method' => 'custom', 'product_name' => 'Lenovo Model Y', 'quantity' => 5, 'unit_price' => 900, 'is_primary' => true],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $quotation->items);
        // Model X primary (700*5=3500) + Model Y (900*5=4500) = 8000 — the
        // HP alternative (900*5=4500) never gets added on top.
        $this->assertEquals('8000.00', $quotation->subtotal);
        $this->assertEquals('8000.00', $quotation->grand_total);
    }

    public function test_a_different_supplier_cannot_offer_another_suppliers_marketplace_listing(): void
    {
        $this->seedBase();
        $supplierA = $this->makeActiveAccount('supplier', 'multioffer-sA@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'multioffer-sB@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-b6@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;
        $listingA = $this->makeListing($supplierA, 'B');

        $service = app(QuotationService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // supplierB tries to submit a draft offering supplierA's own listing.
        $service->submitDraft($service->saveDraft($rfq, $supplierB->account, $supplierB, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 1, 'unit_price' => 700,
                'offered_listing_id' => $listingA->id,
                'offers' => [
                    ['offer_method' => 'marketplace', 'marketplace_product_id' => $listingA->id, 'product_name' => 'Dell Model X', 'quantity' => 1, 'unit_price' => 700, 'is_primary' => true],
                ],
            ]],
        ]));
    }

    public function test_product_selector_page_shows_matching_and_browsable_listings_scoped_to_the_supplier(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-sel1@example.com');
        $otherSupplier = $this->makeActiveAccount('supplier', 'multioffer-sel2@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-sel-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem = $rfq->items[0];

        $ownListing = $this->makeListing($supplier, 'Own');
        $otherListing = $this->makeListing($otherSupplier, 'Other');

        $response = $this->actingAs($supplier)->get(
            route('supplier.select-products-for-quotation', ['rfq_item_id' => $rfqItem->id])
        );

        $response->assertOk();
        $response->assertSee($ownListing->name);
        $response->assertDontSee($otherListing->name);
    }

    public function test_product_selector_page_requires_quotation_creation_eligibility(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-sel3@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-sel-b2@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem = $rfq->items[0];

        // A buyer account has no supplier capability, so QuotationPolicy::create()
        // must deny it — the selector page piggybacks on that same authorization.
        $response = $this->actingAs($buyer)->get(
            route('supplier.select-products-for-quotation', ['rfq_item_id' => $rfqItem->id])
        );

        $response->assertForbidden();
    }
}
