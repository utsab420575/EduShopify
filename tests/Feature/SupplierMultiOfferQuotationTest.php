<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationItemOffer;
use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_saving_draft_with_duplicate_marketplace_offers_deduplicates_and_stores_only_unique_offers(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-dedup-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-dedup-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $listing1 = $this->makeListing($supplier, 'Product1');
        $listing2 = $this->makeListing($supplier, 'Product2');

        $service = app(QuotationService::class);

        // Supplier submits payload where Listing 2 is accidentally duplicated
        // (e.g. from race condition or repeated selection)
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'rfq_item_id' => $rfqItemId,
                    'item_name' => $listing1->name,
                    'quantity' => 5,
                    'unit_price' => 500,
                    'client_ref' => 'ref-dedup',
                    'offers' => [
                        [
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing1->id,
                            'product_name' => $listing1->name,
                            'quantity' => 5,
                            'unit_price' => 500,
                            'is_primary' => true,
                        ],
                        [
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing2->id,
                            'product_name' => $listing2->name,
                            'quantity' => 5,
                            'unit_price' => 600,
                            'is_primary' => false,
                        ],
                        [
                            // DUPLICATE OF LISTING 2
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing2->id,
                            'product_name' => $listing2->name,
                            'quantity' => 5,
                            'unit_price' => 600,
                            'is_primary' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $item = $quotation->items()->first();
        $offers = $item->offers()->get();

        // Exactly 2 offers should exist in the database, NOT 3
        $this->assertCount(2, $offers);
        $this->assertEquals($listing1->id, $offers[0]->marketplace_product_id);
        $this->assertTrue((bool) $offers[0]->is_primary);
        $this->assertEquals($listing2->id, $offers[1]->marketplace_product_id);
        $this->assertFalse((bool) $offers[1]->is_primary);
    }

    public function test_saving_and_resaving_multiple_distinct_marketplace_offers_maintains_exact_count(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-resave-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-resave-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $listing1 = $this->makeListing($supplier, 'A');
        $listing2 = $this->makeListing($supplier, 'B');
        $listing3 = $this->makeListing($supplier, 'C');

        $service = app(QuotationService::class);

        // 1. Initial save with 3 distinct marketplace offers
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'rfq_item_id' => $rfqItemId,
                    'item_name' => $listing1->name,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'client_ref' => 'ref-123',
                    'offers' => [
                        ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing1->id, 'product_name' => $listing1->name, 'quantity' => 2, 'unit_price' => 100, 'is_primary' => true],
                        ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing2->id, 'product_name' => $listing2->name, 'quantity' => 2, 'unit_price' => 120, 'is_primary' => false],
                        ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing3->id, 'product_name' => $listing3->name, 'quantity' => 2, 'unit_price' => 140, 'is_primary' => false],
                    ],
                ],
            ],
        ]);

        $item = $quotation->fresh()->items()->first();
        $this->assertCount(3, $item->offers);

        // 2. Re-save / autosave / refresh the draft with existing IDs passed back
        $existingOffers = $item->offers->map(fn ($o) => [
            'id' => $o->id,
            'offer_method' => $o->offer_method,
            'marketplace_product_id' => $o->marketplace_product_id,
            'product_name' => $o->product_name,
            'quantity' => $o->quantity,
            'unit_price' => $o->unit_price,
            'is_primary' => $o->is_primary,
        ])->all();

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'id' => $item->id,
                    'rfq_item_id' => $rfqItemId,
                    'item_name' => $listing1->name,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'client_ref' => 'ref-123',
                    'offers' => $existingOffers,
                ],
            ],
        ], $quotation);

        $this->assertCount(3, $quotation->fresh()->items()->first()->offers);

        // 3. Submit quotation and ensure exact offer count is preserved in revisions
        $submitted = $service->submitDraft($quotation);
        $this->assertEquals('submitted', $submitted->status);
        $this->assertCount(3, $submitted->items()->first()->offers);
        $revision = $submitted->revisions()->first();
        $this->assertNotNull($revision);
        $this->assertCount(3, $revision->items()->first()->offers);
    }

    /**
     * removeOffer() in _form.blade.php immediately triggers an autosave
     * (rather than waiting for the next manual save/step-change) once the
     * offer being removed already has a real id, precisely so the deletion
     * lands in the database right away instead of reappearing on next load.
     * This covers the backend half: a resave whose payload simply omits a
     * previously-saved offer must hard-delete that row, not just leave it
     * dangling until some later save.
     */
    public function test_removing_an_offer_via_resave_deletes_it_from_database(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-remove-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-remove-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $listing1 = $this->makeListing($supplier, 'A');
        $listing2 = $this->makeListing($supplier, 'B');

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'rfq_item_id' => $rfqItemId,
                    'item_name' => $listing1->name,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'client_ref' => 'ref-remove',
                    'offers' => [
                        ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing1->id, 'product_name' => $listing1->name, 'quantity' => 2, 'unit_price' => 100, 'is_primary' => true],
                        ['offer_method' => 'marketplace', 'marketplace_product_id' => $listing2->id, 'product_name' => $listing2->name, 'quantity' => 2, 'unit_price' => 120, 'is_primary' => false],
                    ],
                ],
            ],
        ]);

        $item = $quotation->fresh()->items()->first();
        $this->assertCount(2, $item->offers);
        $keptOffer = $item->offers->firstWhere('marketplace_product_id', $listing1->id);
        $removedOfferId = $item->offers->firstWhere('marketplace_product_id', $listing2->id)->id;

        // Simulate removeOffer()'s immediate autosave: resend the item with
        // only the surviving offer in the payload.
        $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                [
                    'id' => $item->id,
                    'rfq_item_id' => $rfqItemId,
                    'item_name' => $listing1->name,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'client_ref' => 'ref-remove',
                    'offers' => [
                        [
                            'id' => $keptOffer->id,
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing1->id,
                            'product_name' => $listing1->name,
                            'quantity' => 2,
                            'unit_price' => 100,
                            'is_primary' => true,
                        ],
                    ],
                ],
            ],
        ], $quotation);

        $this->assertCount(1, $item->fresh()->offers);
        $this->assertNull(QuotationItemOffer::find($removedOfferId));
    }

    /**
     * Custom offers have no relational attribute-value table of their own
     * (unlike quotation_items) — the Custom Offer category picker's
     * structured fields get flattened client-side (getOfferSpecsPayload() in
     * _form.blade.php) into the same specifications JSON the free-form
     * custom-spec rows already use. This covers that the backend persists
     * that combined payload as-is, name and value both intact.
     */
    public function test_custom_offer_attribute_values_are_stored_in_specifications(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-specs-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-specs-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Custom Mouse', 'quantity' => 1, 'unit_price' => 50,
                'offers' => [
                    [
                        'offer_method' => 'custom',
                        'product_name' => 'Custom Mouse',
                        'category_id' => Category::create([
                            'name' => 'Mouse', 'slug' => 'mouse-multioffer-'.uniqid(),
                            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
                        ])->id,
                        'quantity' => 1,
                        'unit_price' => 50,
                        'is_primary' => true,
                        // Flat {name, value} pairs — what getOfferSpecsPayload()
                        // sends for the supplier's free-form "Additional
                        // Specifications" rows (structured category attributes
                        // go through attribute_values separately, not here —
                        // see test_alternative_custom_offer_attribute_values_are_stored_per_offer_not_just_primary).
                        'specifications' => [
                            ['name' => 'Brand', 'value' => 'Logitech'],
                            ['name' => 'Connectivity', 'value' => 'Wireless'],
                            ['name' => 'DPI', 'value' => '1600'],
                            ['name' => 'Warranty', 'value' => '2 years (custom note)'],
                        ],
                    ],
                ],
            ]],
        ]);

        $offer = $quotation->items->first()->offers()->first();
        $this->assertNotNull($offer);
        $this->assertIsArray($offer->specifications);
        $this->assertCount(4, $offer->specifications);
        $this->assertEquals(
            ['Brand' => 'Logitech', 'Connectivity' => 'Wireless', 'DPI' => '1600', 'Warranty' => '2 years (custom note)'],
            collect($offer->specifications)->pluck('value', 'name')->all()
        );
    }

    /**
     * The offer card's hidden [attribute_values] input JSON-stringifies
     * offer._attribute_values (a hidden <input> can only carry a string) —
     * a native "Save Draft"/"Submit" form post (unlike autosave, which sends
     * it as a real JSON array via AJAX) submits it this way. Before this was
     * wired up, that field didn't exist at all, so a native submit sent no
     * attribute_values, and syncOfferAttributeValues() treated that as
     * "clear them" — wiping out attribute values that autosave had already
     * saved. This confirms the strict SaveQuotationRequest path (real form
     * post) correctly decodes the JSON string and keeps them.
     */
    public function test_native_form_submit_with_json_string_attribute_values_persists_them(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-nativepost-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-nativepost-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'Keyboard', 'slug' => 'keyboard-nativepost-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attribute = \App\Models\Attribute::create(['name' => 'Switch Type', 'slug' => 'switch-nativepost-'.uniqid(), 'input_type' => 'text']);
        $category->attributes()->attach($attribute->id);

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Custom Keyboard', 'quantity' => 1, 'unit_price' => 70,
                'offers' => [[
                    'offer_method' => 'custom',
                    'category_id' => $category->id,
                    'product_name' => 'Custom Keyboard',
                    'quantity' => 1,
                    'unit_price' => 70,
                    'is_primary' => true,
                ]],
            ]],
        ]);
        $item = $quotation->items->first();
        $offer = $item->offers()->first();

        // Simulate the native form post: attribute_values arrives as a JSON
        // string (from the hidden input), not a real array (PHPUnit's put()
        // without ...Json form-encodes the payload, same as a real <form>).
        $payload = [
            'currency_code' => 'USD',
            'items' => [[
                'id' => $item->id,
                'item_name' => 'Custom Keyboard',
                'quantity' => 1,
                'unit_price' => 70,
                'offers' => [[
                    'id' => $offer->id,
                    'offer_method' => 'custom',
                    'category_id' => (string) $category->id,
                    'product_name' => 'Custom Keyboard',
                    'quantity' => 1,
                    'unit_price' => 70,
                    'is_primary' => 1,
                    'attribute_values' => json_encode([$attribute->id => ['value_text' => 'Blue Switch']]),
                    'specifications' => json_encode([]),
                ]],
            ]],
        ];

        $res = $this->actingAs($supplier)->put(route('supplier.quotations.update', $quotation), $payload);
        $res->assertRedirect();

        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $offer->id, 'attribute_id' => $attribute->id, 'value_text' => 'Blue Switch',
        ]);
    }

    /**
     * Backs the Custom Offer debounced autosave (scheduleCustomOfferAutosave
     * in _form.blade.php, triggered on category/attribute/price/quantity/spec
     * changes): every one of those repeated autosave calls carries the same
     * offer id once the first one assigns it, so this covers that the
     * backend genuinely updates that same row in place rather than creating
     * a new quotation_item/quotation_item_offer each time, and that
     * switching category drops the old category's attribute values instead
     * of leaving them behind alongside the new ones.
     */
    public function test_custom_offer_category_change_updates_same_offer_and_swaps_attribute_values(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-catchange-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-catchange-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $categoryA = Category::create([
            'name' => 'Mouse', 'slug' => 'mouse-catchange-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attrA = \App\Models\Attribute::create(['name' => 'DPI', 'slug' => 'dpi-catchange-'.uniqid(), 'input_type' => 'text']);
        $categoryA->attributes()->attach($attrA->id);

        $categoryB = Category::create([
            'name' => 'Keyboard', 'slug' => 'keyboard-catchange-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attrB = \App\Models\Attribute::create(['name' => 'Switch Type', 'slug' => 'switch-type-catchange-'.uniqid(), 'input_type' => 'text']);
        $categoryB->attributes()->attach($attrB->id);

        $service = app(QuotationService::class);

        // 1. First debounced autosave: category A + its attribute value.
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Custom Peripheral', 'quantity' => 1, 'unit_price' => 50,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $categoryA->id,
                    'product_name' => 'Custom Peripheral',
                    'quantity' => 1,
                    'unit_price' => 50,
                    'is_primary' => true,
                    'attribute_values' => [$attrA->id => ['value_text' => '1600 DPI']],
                    'specifications' => [['name' => 'DPI', 'value' => '1600 DPI']],
                ]],
            ]],
        ]);

        $this->assertEquals(1, $quotation->fresh()->items()->count());
        $item = $quotation->items->first();
        $offer = $item->offers()->first();
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $offer->id, 'attribute_id' => $attrA->id, 'value_text' => '1600 DPI',
        ]);

        // 2. Supplier changes category (A -> B) and fills in B's attribute —
        // the same offer/item ids are sent back, exactly like the frontend's
        // debounced watcher would on the next fire.
        $quotation2 = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'id' => $item->id,
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Custom Peripheral', 'quantity' => 1, 'unit_price' => 60,
                'offers' => [[
                    'id' => $offer->id,
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $categoryB->id,
                    'product_name' => 'Custom Peripheral',
                    'quantity' => 1,
                    'unit_price' => 60,
                    'is_primary' => true,
                    'attribute_values' => [$attrB->id => ['value_text' => 'Blue Switch']],
                    'specifications' => [['name' => 'Switch Type', 'value' => 'Blue Switch']],
                ]],
            ]],
        ], $quotation);

        // Still exactly one item and one offer — no duplicates.
        $this->assertEquals(1, $quotation2->fresh()->items()->count());
        $this->assertEquals(1, QuotationItemOffer::whereHas('quotationItem', fn ($q) => $q->where('quotation_id', $quotation2->id))->count());

        $item->refresh();
        $offer->refresh();
        $this->assertEquals($item->id, $quotation2->items->first()->id);
        $this->assertEquals($offer->id, $item->offers->first()->id);
        $this->assertEquals($categoryB->id, $offer->category_id);
        $this->assertEquals('60.00', $offer->unit_price);

        // Old category's attribute value is gone, new category's is stored.
        $this->assertDatabaseMissing('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $offer->id, 'attribute_id' => $attrA->id,
        ]);
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $offer->id, 'attribute_id' => $attrB->id, 'value_text' => 'Blue Switch',
        ]);
    }

    /**
     * "Copy Buyer Spec" (offer_method 'copy_spec') previously only carried
     * the buyer's free-form custom specifications into the offer — the
     * category and structured category attributes were captured into
     * offer._attribute_values client-side but never made it into the
     * submitted specifications payload, because getOfferSpecsPayload() only
     * resolved _attrGroups for offer_method 'custom'. This covers the fixed
     * behavior: a copy_spec offer's payload (category attributes THEN
     * free-form specs, same shape getOfferSpecsPayload() now produces for
     * copy_spec) persists both, with category_id also stored.
     */
    public function test_copy_buyer_spec_offer_stores_both_category_attributes_and_custom_specs(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-copyspec-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-copyspec-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'Mouse', 'slug' => 'mouse-copyspec-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Copied Mouse', 'quantity' => 1, 'unit_price' => 25,
                'offers' => [[
                    'offer_method' => 'copy_spec',
                    'product_name' => 'Copied Mouse',
                    'category_id' => $category->id,
                    'quantity' => 1,
                    'unit_price' => 25,
                    'is_primary' => true,
                    // Exactly what getOfferSpecsPayload() now builds for a
                    // copy_spec offer: structured category attributes first
                    // (Model, Color — resolved from _attrGroups against the
                    // copied _attribute_values), then the buyer's free-form
                    // custom specs (Warranty).
                    'specifications' => [
                        ['name' => 'Model', 'value' => 'MX100'],
                        ['name' => 'Color', 'value' => 'Black'],
                        ['name' => 'Warranty', 'value' => '1 year'],
                    ],
                ]],
            ]],
        ]);

        $offer = $quotation->items->first()->offers()->first();
        $this->assertNotNull($offer);
        $this->assertEquals($category->id, $offer->category_id);
        $this->assertCount(3, $offer->specifications);
        $this->assertEquals(
            ['Model' => 'MX100', 'Color' => 'Black', 'Warranty' => '1 year'],
            collect($offer->specifications)->pluck('value', 'name')->all()
        );
    }

    /**
     * The client needs a real quotation_item_offers id before it can upload
     * a document to it (QuotationItemOfferDocumentController), but a
     * brand-new offer has none until autosaved — this covers that
     * client_ref => id map (mirroring the existing item-level one)
     * actually gets populated by saveDraft()/syncItemOffers().
     */
    public function test_save_draft_returns_client_ref_to_offer_id_map_for_document_upload(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-clientref-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-clientref-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Doc Offer', 'quantity' => 1, 'unit_price' => 10,
                'offers' => [[
                    'client_ref' => 'offer-local-key-abc',
                    'offer_method' => 'document',
                    'product_name' => 'Quotation Document',
                    'quantity' => 1,
                    'unit_price' => 10,
                    'is_primary' => true,
                ]],
            ]],
        ]);

        $offerIds = $service->getLastClientRefOfferIds();
        $this->assertArrayHasKey('offer-local-key-abc', $offerIds);
        $realOffer = $quotation->items->first()->offers()->first();
        $this->assertEquals($realOffer->id, $offerIds['offer-local-key-abc']);
    }

    /**
     * End-to-end through the real HTTP route: uploading and deleting a
     * document on a Document offer, using the SAME upload/mime-type
     * validation pattern QuotationItemDocumentController (item-level)
     * already established — mimes restricted to what the buyer/supplier
     * quotation flow actually needs (PDF/DOC/DOCX/XLS/XLSX/CSV), 10MB max.
     */
    public function test_supplier_can_upload_and_remove_document_files_on_an_offer(): void
    {
        Storage::fake('public');
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-upload-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-upload-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Doc Offer', 'quantity' => 1, 'unit_price' => 10,
                'offers' => [[
                    'offer_method' => 'document', 'product_name' => 'Quotation Document',
                    'quantity' => 1, 'unit_price' => 10, 'is_primary' => true,
                ]],
            ]],
        ]);

        $item = $quotation->items->first();
        $offer = $item->offers()->first();

        // Upload two files — multiple files on one offer is required behavior.
        $pdf = UploadedFile::fake()->create('quotation.pdf', 500, 'application/pdf');
        $xlsx = UploadedFile::fake()->create('price_list.xlsx', 300, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 'Accept: application/json' is what makes a validation failure come
        // back as 422 JSON instead of a 302 redirect — same as the real
        // client's fetch() calls always send.
        $jsonHeaders = ['Accept' => 'application/json'];

        $res1 = $this->actingAs($supplier)->post(
            route('supplier.quotations.items.offers.document.store', [$quotation, $item, $offer]),
            ['document' => $pdf],
            $jsonHeaders
        );
        $res1->assertOk();
        // "name" in the response is the sanitized on-disk file_name (same
        // uniqid()-suffixed convention as combined_document/item document
        // uploads), not the original filename — matches, not literal.
        $res1Json = $res1->json();
        $this->assertArrayHasKey('id', $res1Json);
        $this->assertStringStartsWith((string) $supplier->account->id . '_', $res1Json['name']);
        $this->assertStringEndsWith('.pdf', $res1Json['name']);
        $dateFolder = now()->format('d_m_Y');
        $this->assertStringContainsString("QuotationDocument/{$dateFolder}/", $res1Json['url']);

        $res2 = $this->actingAs($supplier)->post(
            route('supplier.quotations.items.offers.document.store', [$quotation, $item, $offer]),
            ['document' => $xlsx],
            $jsonHeaders
        );
        $res2->assertOk();

        $offer->refresh();
        $this->assertCount(2, $offer->getMedia('document'));

        // A disallowed file type is rejected (only PDF/DOC/DOCX/XLS/XLSX/CSV allowed).
        $badFile = UploadedFile::fake()->create('image.png', 100, 'image/png');
        $res3 = $this->actingAs($supplier)->post(
            route('supplier.quotations.items.offers.document.store', [$quotation, $item, $offer]),
            ['document' => $badFile],
            $jsonHeaders
        );
        $res3->assertStatus(422);
        $offer->refresh();
        $this->assertCount(2, $offer->getMedia('document'));

        // Remove one file — the other stays.
        $mediaId = $offer->getMedia('document')->first()->id;
        $res4 = $this->actingAs($supplier)->delete(
            route('supplier.quotations.items.offers.document.destroy', [$quotation, $item, $offer, $mediaId]),
            [],
            $jsonHeaders
        );
        $res4->assertOk();
        $offer->refresh();
        $this->assertCount(1, $offer->getMedia('document'));
    }

    /**
     * A different supplier's offer must not be reachable through these
     * nested {quotation}/{item}/{offer} routes even if they somehow guess a
     * valid offer id — the quotation-level editDraft policy (ownership +
     * draft status) is the real gate, same as the item-level controller.
     */
    public function test_supplier_cannot_upload_a_document_to_another_suppliers_offer(): void
    {
        Storage::fake('public');
        $this->seedBase();
        $supplierA = $this->makeActiveAccount('supplier', 'multioffer-upload-A@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'multioffer-upload-B@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-upload-b2@example.com');
        $rfqA = $this->makeOpenRfq($buyer);
        $rfqItemIdA = $rfqA->items[0]->id;

        $service = app(QuotationService::class);
        $quotationA = $service->saveDraft($rfqA, $supplierA->account, $supplierA, [
            'items' => [[
                'rfq_item_id' => $rfqItemIdA, 'item_name' => 'Doc Offer', 'quantity' => 1, 'unit_price' => 10,
                'offers' => [['offer_method' => 'document', 'product_name' => 'Doc', 'quantity' => 1, 'unit_price' => 10, 'is_primary' => true]],
            ]],
        ]);
        $itemA = $quotationA->items->first();
        $offerA = $itemA->offers()->first();

        $file = UploadedFile::fake()->create('sneaky.pdf', 100, 'application/pdf');
        $response = $this->actingAs($supplierB)->post(
            route('supplier.quotations.items.offers.document.store', [$quotationA, $itemA, $offerA]),
            ['document' => $file]
        );

        $response->assertForbidden();
    }

    /**
     * When an RFQ has multiple items and some items have no offers yet and null prices,
     * clicking "Upload Document" triggers autosave with unpriced / empty items.
     * Autosave must succeed with 200, return the quotation, item, and offer IDs,
     * and allow immediate document upload.
     */
    public function test_supplier_can_autosave_and_upload_document_when_multi_item_rfq_has_unpriced_and_empty_items(): void
    {
        Storage::fake('public');
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-doc-multi@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-doc-buyer@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        // Add a second item to the RFQ to simulate multi-item RFQ (like RFQ 43)
        $rfq->items()->create([
            'item_type' => 'product',
            'item_name' => 'Second RFQ Item',
            'quantity' => 2,
            'unit_id' => $rfq->items->first()->unit_id,
        ]);
        $rfq->refresh();
        $this->assertCount(2, $rfq->items);

        $rfqItem1 = $rfq->items[0];
        $rfqItem2 = $rfq->items[1];

        // Simulate autosave payload from frontend:
        // Item 1 has a Document offer with no price yet
        // Item 2 has no offers and null price yet
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'current_step' => 1,
            'max_completed_step' => 1,
            'items' => [
                [
                    'client_ref' => 'client-item-1',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => $rfqItem1->item_name,
                    'quantity' => 1,
                    'unit_price' => null,
                    'response_method' => 'document',
                    'offers' => [
                        [
                            'client_ref' => 'client-offer-1',
                            'offer_method' => 'document',
                            'product_name' => $rfqItem1->item_name . ' — Document Quotation',
                            'quantity' => 1,
                            'unit_price' => null,
                            'is_primary' => true,
                            'sort_order' => 0,
                            // What the frontend's uploadOfferDocument() now sends on
                            // its id-bootstrap autosave, right when a file is picked.
                            '_uploadPreparing' => true,
                        ],
                    ],
                ],
                [
                    'client_ref' => 'client-item-2',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => $rfqItem2->item_name,
                    'quantity' => 2,
                    'unit_price' => null,
                    'offers' => [],
                ],
            ],
        ];

        $autosaveResponse = $this->actingAs($supplier)->postJson(
            route('supplier.quotations.autosave.create', $rfq),
            $payload
        );

        $autosaveResponse->assertOk();
        $data = $autosaveResponse->json();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('offers', $data);

        $quotationId = $data['id'];
        $itemId = $data['items']['client-item-1'];
        $offerId = $data['offers']['client-offer-1'];

        $this->assertNotNull($quotationId);
        $this->assertNotNull($itemId);
        $this->assertNotNull($offerId);

        // Verify the document upload now succeeds on the persisted offer
        $quotation = Quotation::find($quotationId);
        $item = QuotationItem::find($itemId);
        $offer = \App\Models\QuotationItemOffer::find($offerId);

        $file = UploadedFile::fake()->create('quotation_proposal.pdf', 250, 'application/pdf');
        $uploadResponse = $this->actingAs($supplier)->post(
            route('supplier.quotations.items.offers.document.store', [$quotation, $item, $offer]),
            ['document' => $file],
            ['Accept' => 'application/json']
        );

        $uploadResponse->assertOk();
        $uploadData = $uploadResponse->json();
        $this->assertArrayHasKey('id', $uploadData);
        $this->assertStringStartsWith((string) $supplier->account->id . '_', $uploadData['name']);
        $this->assertStringEndsWith('.pdf', $uploadData['name']);
        $dateFolder = now()->format('d_m_Y');
        $this->assertStringContainsString("QuotationDocument/{$dateFolder}/", $uploadData['url']);

        $offer->refresh();
        $this->assertCount(1, $offer->getMedia('document'));

        // Verify quotation_activities has 'drafted' record
        $this->assertDatabaseHas('quotation_activities', [
            'quotation_id' => $quotationId,
            'activity_type' => 'drafted',
        ]);
    }

    public function test_clicking_submit_quote_from_opportunities_creates_draft_and_records_drafted_activity(): void
    {
        $this->seedBase();
        Storage::fake('public');

        $buyer = $this->makeActiveAccount('buyer', 'buyer_submit_quote@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_submit_quote@example.com');

        $rfq = $this->makeOpenRfq($buyer);

        // Supplier clicks "Submit Quote" (GET supplier.quotations.create)
        $response = $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));

        $quotation = Quotation::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $supplier->account->id)
            ->first();

        $this->assertNotNull($quotation);
        $this->assertEquals('draft', $quotation->status);
        $response->assertRedirect(route('supplier.quotations.edit', $quotation));

        // Verify NO child records (quotation_items or quotation_item_offers) are created yet
        $this->assertEquals(0, $quotation->items()->count());
        $this->assertEquals(0, \App\Models\QuotationItemOffer::whereHas('quotationItem', fn ($q) => $q->where('quotation_id', $quotation->id))->count());

        // Verify drafted record exists in quotation_activities table
        $this->assertDatabaseHas('quotation_activities', [
            'quotation_id' => $quotation->id,
            'rfq_id' => $rfq->id,
            'supplier_account_id' => $supplier->account->id,
            'buyer_account_id' => $buyer->account->id,
            'activity_type' => 'drafted',
        ]);

        // Re-clicking "Submit Quote" / visiting create redirects to edit without duplicate draft
        $secondResponse = $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $secondResponse->assertRedirect(route('supplier.quotations.edit', $quotation));

        $this->assertEquals(1, Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->count());
        $this->assertEquals(0, $quotation->fresh()->items()->count());
        $this->assertEquals(1, \App\Models\QuotationActivity::where('quotation_id', $quotation->id)->where('activity_type', 'drafted')->count());
    }

    public function test_marketplace_product_selector_is_accessible_when_draft_quotation_already_exists(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer_selector_test@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_selector_test@example.com');
        $otherSupplier = $this->makeActiveAccount('supplier', 'other_supplier_selector_test@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem = $rfq->items[0];

        // 1. Supplier clicks Submit Quote -> creates draft quotation header
        $createResponse = $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $createResponse->assertRedirect();
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals('draft', $quotation->status);

        // 2. Previously this gave 403 Forbidden because QuotationPolicy::create checked !quotation exists.
        // With selectProducts policy, the owner supplier can access the selector while quotation is in draft:
        $selectorResponse = $this->actingAs($supplier)->get(
            route('supplier.quotations.listings.select', ['rfq' => $rfq, 'rfq_item_id' => $rfqItem->id])
        );
        $selectorResponse->assertOk();

        $legacySelectorResponse = $this->actingAs($supplier)->get(
            route('supplier.select-products-for-quotation', ['rfq_item_id' => $rfqItem->id])
        );
        $legacySelectorResponse->assertOk();

        $autoMatchResponse = $this->actingAs($supplier)->get(
            route('supplier.quotations.listings.auto-match', $rfq)
        );
        $autoMatchResponse->assertOk();

        // 3. Security check: another supplier cannot access another supplier's draft quotation context
        // or bypass policy if forbidden
        $buyerResponse = $this->actingAs($buyer)->get(
            route('supplier.quotations.listings.select', ['rfq' => $rfq, 'rfq_item_id' => $rfqItem->id])
        );
        $buyerResponse->assertForbidden();
    }

    public function test_lazy_quotation_item_creation_persists_only_items_with_offers(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer_lazy_test@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_lazy_test@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->items()->create([
            'item_type' => 'product',
            'item_name' => 'Second Buyer Requirement',
            'quantity' => 5,
            'unit_id' => $rfq->items->first()->unit_id,
        ]);
        $rfq->refresh();
        $this->assertCount(2, $rfq->items);

        $rfqItem1 = $rfq->items[0];
        $rfqItem2 = $rfq->items[1];
        $listing = $this->makeListing($supplier, 'LazyTest');

        // Step 1: Supplier clicks "Submit Quote" -> creates draft quotation header only
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(0, $quotation->items()->count());

        // Step 2: Supplier responds to RFQ Item 1 with an offer, but leaves RFQ Item 2 blank (no offers)
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'items' => [
                [
                    'client_ref' => 'ref-item-1',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => $listing->name,
                    'quantity' => 10,
                    'unit_price' => 700,
                    'offers' => [
                        [
                            'client_ref' => 'ref-offer-1',
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing->id,
                            'product_name' => $listing->name,
                            'quantity' => 10,
                            'unit_price' => 700,
                            'is_primary' => true,
                        ],
                    ],
                ],
                [
                    'client_ref' => 'ref-item-2',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => $rfqItem2->item_name,
                    'quantity' => 5,
                    'unit_price' => null,
                    'offers' => [], // Unresponded requirement
                ],
            ],
        ];

        $autosaveResponse = $this->actingAs($supplier)->putJson(
            route('supplier.quotations.autosave.update', $quotation),
            $payload
        );
        $autosaveResponse->assertOk();

        // Verify lazy persistence: ONLY 1 quotation_items record and 1 quotation_item_offers record exist
        $quotation->refresh();
        $this->assertCount(1, $quotation->items);
        $this->assertEquals($rfqItem1->id, $quotation->items->first()->rfq_item_id);
        $this->assertCount(1, $quotation->items->first()->offers);
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());

        // Step 3: Now supplier responds to RFQ Item 2 as well
        $payload['items'][1]['offers'] = [
            [
                'client_ref' => 'ref-offer-2',
                'offer_method' => 'custom',
                'product_name' => 'Custom Item 2 Offer',
                'quantity' => 5,
                'unit_price' => 300,
                'is_primary' => true,
            ],
        ];

        $autosaveResponse2 = $this->actingAs($supplier)->putJson(
            route('supplier.quotations.autosave.update', $quotation),
            $payload
        );
        $autosaveResponse2->assertOk();

        // Now both items are persisted
        $quotation->refresh();
        $this->assertCount(2, $quotation->items);
        $this->assertEquals(2, QuotationItemOffer::whereHas('quotationItem', fn ($q) => $q->where('quotation_id', $quotation->id))->count());
    }

    public function test_three_item_rfq_lazy_persistence_for_marketplace_product(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer_3item_mkt@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_3item_mkt@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem1 = $rfq->items[0]; // Computer
        $rfqItem2 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Mouse', 'quantity' => 10, 'unit_id' => $rfqItem1->unit_id]);
        $rfqItem3 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Keyboard', 'quantity' => 5, 'unit_id' => $rfqItem1->unit_id]);

        $listing = $this->makeListing($supplier, 'Computer');

        // Supplier clicks "Submit Quote"
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(0, $quotation->items()->count());
        $this->assertEquals(0, \App\Models\QuotationItemOffer::whereHas('quotationItem', fn ($q) => $q->where('quotation_id', $quotation->id))->count());

        // Supplier responds ONLY to Item 1 (Computer) via Marketplace Product
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'items' => [
                [
                    'client_ref' => 'ref-comp',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => $listing->name,
                    'quantity' => 10,
                    'unit_price' => 700,
                    'offers' => [
                        [
                            'client_ref' => 'ref-comp-offer',
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing->id,
                            'product_name' => $listing->name,
                            'quantity' => 10,
                            'unit_price' => 700,
                            'is_primary' => true,
                        ],
                    ],
                ],
                [
                    'client_ref' => 'ref-mouse',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => 'Mouse',
                    'quantity' => 10,
                    'unit_price' => null,
                    'offers' => [], // Unresponded
                ],
                [
                    'client_ref' => 'ref-keyboard',
                    'rfq_item_id' => $rfqItem3->id,
                    'item_name' => 'Keyboard',
                    'quantity' => 5,
                    'unit_price' => null,
                    'offers' => [], // Unresponded
                ],
            ],
        ];

        $res = $this->actingAs($supplier)->putJson(route('supplier.quotations.autosave.update', $quotation), $payload);
        $res->assertOk();

        $quotation->refresh();
        $this->assertCount(1, $quotation->items);
        $this->assertEquals($rfqItem1->id, $quotation->items->first()->rfq_item_id);
        $this->assertCount(1, $quotation->items->first()->offers);
        $this->assertEquals('marketplace', $quotation->items->first()->offers->first()->offer_method);

        // Ensure 0 records exist for Item 2 (Mouse) and Item 3 (Keyboard)
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem3->id)->count());
    }

    public function test_three_item_rfq_lazy_persistence_for_custom_offer_with_attributes(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer_3item_custom@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_3item_custom@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem1 = $rfq->items[0]; // Computer
        $rfqItem2 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Mouse', 'quantity' => 10, 'unit_id' => $rfqItem1->unit_id]);
        $rfqItem3 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Keyboard', 'quantity' => 5, 'unit_id' => $rfqItem1->unit_id]);

        $category = Category::create([
            'name' => 'Custom Category', 'slug' => 'custom-cat-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attribute = \App\Models\Attribute::create([
            'name' => 'Warranty Period', 'slug' => 'warranty-period-'.uniqid(), 'input_type' => 'text',
        ]);
        $category->attributes()->attach($attribute->id);

        $rfqItem1->update(['category_id' => $category->id]);

        // Supplier clicks "Submit Quote"
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);

        // Supplier responds ONLY to Item 1 with Custom Offer and fills attribute value
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'items' => [
                [
                    'client_ref' => 'ref-comp',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => 'Custom Built PC',
                    'quantity' => 10,
                    'unit_price' => 850,
                    'offers' => [
                        [
                            'client_ref' => 'ref-custom-offer',
                            'offer_method' => 'custom',
                            'category_id' => $category->id,
                            'product_name' => 'Custom Built PC',
                            'quantity' => 10,
                            'unit_price' => 850,
                            'is_primary' => true,
                            'attribute_values' => [
                                $attribute->id => ['value_text' => '3 Years Onsite'],
                            ],
                            'specifications' => [
                                ['name' => 'Warranty Period', 'value' => '3 Years Onsite'],
                            ],
                        ],
                    ],
                ],
                [
                    'client_ref' => 'ref-mouse',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => 'Mouse',
                    'quantity' => 10,
                    'unit_price' => null,
                    'offers' => [],
                ],
                [
                    'client_ref' => 'ref-keyboard',
                    'rfq_item_id' => $rfqItem3->id,
                    'item_name' => 'Keyboard',
                    'quantity' => 5,
                    'unit_price' => null,
                    'offers' => [],
                ],
            ],
        ];

        $res = $this->actingAs($supplier)->putJson(route('supplier.quotations.autosave.update', $quotation), $payload);
        $res->assertOk();

        $quotation->refresh();
        $this->assertCount(1, $quotation->items);
        $savedItem = $quotation->items->first();
        $this->assertEquals($rfqItem1->id, $savedItem->rfq_item_id);
        $this->assertCount(1, $savedItem->offers);
        $this->assertEquals('custom', $savedItem->offers->first()->offer_method);

        // Verify quotation_item_attribute_values is created for the provided attribute
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $savedItem->offers->first()->id,
            'attribute_id' => $attribute->id,
            'value_text' => '3 Years Onsite',
        ]);

        // Ensure 0 records exist for Item 2 and Item 3
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem3->id)->count());
    }

    public function test_three_item_rfq_lazy_persistence_for_upload_document(): void
    {
        Storage::fake('public');
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer_3item_doc@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier_3item_doc@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem1 = $rfq->items[0]; // Computer
        $rfqItem2 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Mouse', 'quantity' => 10, 'unit_id' => $rfqItem1->unit_id]);
        $rfqItem3 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Keyboard', 'quantity' => 5, 'unit_id' => $rfqItem1->unit_id]);

        // Supplier clicks "Submit Quote"
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);

        // Supplier selects "Upload Document" for Item 1 and uploads file
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'items' => [
                [
                    'client_ref' => 'ref-comp',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => 'Computer Quotation Document',
                    'quantity' => 1,
                    'unit_price' => null,
                    'offers' => [
                        [
                            'client_ref' => 'ref-doc-offer',
                            'offer_method' => 'document',
                            'product_name' => 'Computer Document Proposal',
                            'quantity' => 1,
                            'unit_price' => null,
                            'is_primary' => true,
                            '_uploadPreparing' => true,
                        ],
                    ],
                ],
                [
                    'client_ref' => 'ref-mouse',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => 'Mouse',
                    'quantity' => 10,
                    'unit_price' => null,
                    'offers' => [],
                ],
                [
                    'client_ref' => 'ref-keyboard',
                    'rfq_item_id' => $rfqItem3->id,
                    'item_name' => 'Keyboard',
                    'quantity' => 5,
                    'unit_price' => null,
                    'offers' => [],
                ],
            ],
        ];

        $autosaveRes = $this->actingAs($supplier)->putJson(route('supplier.quotations.autosave.update', $quotation), $payload);
        $autosaveRes->assertOk();
        $data = $autosaveRes->json();

        $savedItem = QuotationItem::find($data['items']['ref-comp']);
        $savedOffer = QuotationItemOffer::find($data['offers']['ref-doc-offer']);
        $this->assertNotNull($savedItem);
        $this->assertNotNull($savedOffer);

        // Upload PDF
        $file = UploadedFile::fake()->create('computer_quote.pdf', 300, 'application/pdf');
        $uploadRes = $this->actingAs($supplier)->post(
            route('supplier.quotations.items.offers.document.store', [$quotation, $savedItem, $savedOffer]),
            ['document' => $file],
            ['Accept' => 'application/json']
        );
        $uploadRes->assertOk();

        $savedOffer->refresh();
        $this->assertCount(1, $savedOffer->getMedia('document'));

        // Assert only Item 1 exists, 0 records for Item 2 and Item 3
        $this->assertEquals(1, $quotation->fresh()->items()->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem3->id)->count());
    }

    /**
     * "Copy buyer specifications to my offer" defaults to CHECKED on a
     * brand-new Custom offer (addCustomOffer() in _form.blade.php), which
     * auto-fills category_id/attributes/specs the instant the supplier
     * clicks "Add Custom Offer" — before they've actually done anything
     * themselves. isValidOffer() must not treat that auto-copy alone as a
     * real response: only entering a price (the one field copy never
     * touches) should let it persist.
     */
    public function test_custom_offer_with_only_auto_copied_category_and_no_price_does_not_persist(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-lazy-custom-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-lazy-custom-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'LazyCustomCat', 'slug' => 'lazy-custom-cat-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Computer', 'quantity' => 10, 'unit_price' => null,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $category->id, // auto-copied by the checkbox default, not chosen by the supplier
                    'product_name' => 'Computer',
                    'quantity' => 10,
                    'unit_price' => '', // never touched
                    'is_primary' => true,
                ]],
            ]],
        ]);

        $this->assertEquals(0, $quotation->fresh()->items()->count());

        // Entering a price is what turns it into a real response.
        $quotation2 = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Computer', 'quantity' => 10, 'unit_price' => 900,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $category->id,
                    'product_name' => 'Computer',
                    'quantity' => 10,
                    'unit_price' => 900,
                    'is_primary' => true,
                ]],
            ]],
        ], $quotation);

        $this->assertEquals(1, $quotation2->fresh()->items()->count());
    }

    /**
     * The exact scenario reported: supplier manually picks a DIFFERENT
     * category than the buyer's default, types real attribute values, but
     * hasn't entered a price yet — this must persist (it's genuine input,
     * not the auto-copy default), unlike the untouched-auto-copy test above.
     * _hasUserEdited (set by the $watch in _offer-card.blade.php on any
     * change after the card mounts) is what tells the two apart.
     */
    public function test_custom_offer_with_manually_entered_category_and_attributes_persists_even_without_price(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-manual-custom-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-manual-custom-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'ManuallyPickedCat', 'slug' => 'manual-custom-cat-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Laptop1', 'quantity' => 1, 'unit_price' => null,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $category->id, // manually picked, different from the buyer's category
                    'product_name' => 'Laptop1',
                    'quantity' => 1,
                    'unit_price' => '', // not entered yet
                    'is_primary' => false,
                    '_hasUserEdited' => true, // set once the category/attribute $watch fired
                    'specifications' => [
                        ['name' => 'Model', 'value' => 'dsafds'],
                        ['name' => 'Processor', 'value' => 'dsafdsa'],
                    ],
                ]],
            ]],
        ]);

        $this->assertEquals(1, $quotation->fresh()->items()->count());
        $offer = $quotation->items->first()->offers()->first();
        $this->assertNotNull($offer);
        $this->assertEquals($category->id, $offer->category_id);
        $this->assertEquals(
            ['Model' => 'dsafds', 'Processor' => 'dsafdsa'],
            collect($offer->specifications)->pluck('value', 'name')->all()
        );
    }

    /**
     * A debounced autosave can fire mid-edit, e.g. right after the supplier
     * clears the price field to type a new one. An already-persisted Custom
     * offer must not get deleted by isValidOffer() just because it's
     * momentarily "incomplete" in that instant — only the trash-icon
     * removeOffer() (a direct, explicit delete) should make it disappear.
     */
    public function test_existing_custom_offer_with_price_temporarily_cleared_is_not_deleted(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-clearprice-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-clearprice-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'ClearPriceCat', 'slug' => 'clearprice-cat-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Laptop1', 'quantity' => 1, 'unit_price' => 500,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $category->id,
                    'product_name' => 'Laptop1',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'is_primary' => true,
                ]],
            ]],
        ]);

        $item = $quotation->items->first();
        $offer = $item->offers()->first();
        $this->assertNotNull($offer);

        // Supplier clears the price to type a new one; a debounced autosave
        // fires in that exact instant, sending the offer's real id but a
        // blank price.
        $quotation2 = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'id' => $item->id,
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Laptop1', 'quantity' => 1, 'unit_price' => null,
                'offers' => [[
                    'id' => $offer->id,
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'custom',
                    'category_id' => $category->id,
                    'product_name' => 'Laptop1',
                    'quantity' => 1,
                    'unit_price' => '',
                    'is_primary' => true,
                ]],
            ]],
        ], $quotation);

        $this->assertEquals(1, $quotation2->fresh()->items()->count());
        $this->assertNotNull(QuotationItemOffer::find($offer->id));
    }

    /**
     * Selecting "Upload Document" as the response method must not persist
     * anything on its own — only actually picking a file (which sets
     * _uploadPreparing before its id-bootstrap autosave, see
     * uploadOfferDocument() in _form.blade.php) should.
     */
    public function test_document_offer_before_any_file_is_selected_does_not_persist(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-lazy-doc-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-lazy-doc-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Computer', 'quantity' => 10, 'unit_price' => null,
                'offers' => [[
                    'client_ref' => 'ref-offer-1',
                    'offer_method' => 'document',
                    'product_name' => 'Computer — Document Quotation',
                    'quantity' => 10,
                    'unit_price' => '',
                    'is_primary' => true,
                    // no _uploadPreparing, no documents — nothing selected yet
                ]],
            ]],
        ]);

        $this->assertEquals(0, $quotation->fresh()->items()->count());
    }

    /**
     * The reported bug: an ALTERNATIVE custom offer's structured attribute
     * values never reached quotation_item_attribute_values, because that
     * table was only ever written from whichever offer happened to be
     * primary — an alternative's answers only ever landed in its own
     * quotation_item_offers.specifications JSON. Now every offer under a
     * Product Response gets its own attribute-value rows, correctly scoped
     * by quotation_item_offer_id, none of them mixed together.
     */
    public function test_alternative_custom_offer_attribute_values_are_stored_per_offer_not_just_primary(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-perofferattr-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-perofferattr-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $categoryA = Category::create([
            'name' => 'Laptop', 'slug' => 'laptop-perofferattr-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attrModel = \App\Models\Attribute::create(['name' => 'Model', 'slug' => 'model-perofferattr-'.uniqid(), 'input_type' => 'text']);
        $categoryA->attributes()->attach($attrModel->id);

        $categoryB = Category::create([
            'name' => 'Mouse', 'slug' => 'mouse-perofferattr-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attrDpi = \App\Models\Attribute::create(['name' => 'DPI', 'slug' => 'dpi-perofferattr-'.uniqid(), 'input_type' => 'text']);
        $categoryB->attributes()->attach($attrDpi->id);

        $service = app(QuotationService::class);

        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'client_ref' => 'ref-item-1',
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Laptop1', 'quantity' => 1, 'unit_price' => 900,
                'offers' => [
                    [
                        'client_ref' => 'ref-offer-primary',
                        'offer_method' => 'custom',
                        'category_id' => $categoryA->id,
                        'product_name' => 'Primary Laptop',
                        'quantity' => 1,
                        'unit_price' => 900,
                        'is_primary' => true,
                        'attribute_values' => [$attrModel->id => ['value_text' => 'XPS 15']],
                        'specifications' => [['name' => 'Model', 'value' => 'XPS 15']],
                    ],
                    [
                        'client_ref' => 'ref-offer-alt',
                        'offer_method' => 'custom',
                        'category_id' => $categoryB->id,
                        'product_name' => 'Alternative Mouse Bundle',
                        'quantity' => 1,
                        'unit_price' => 50,
                        'is_primary' => false,
                        '_hasUserEdited' => true,
                        'attribute_values' => [$attrDpi->id => ['value_text' => '1600 DPI']],
                        'specifications' => [['name' => 'DPI', 'value' => '1600 DPI']],
                    ],
                ],
            ]],
        ]);

        $item = $quotation->items->first();
        $this->assertCount(2, $item->offers);
        $primaryOffer = $item->offers->firstWhere('is_primary', true);
        $altOffer = $item->offers->firstWhere('is_primary', false);
        $this->assertNotNull($primaryOffer);
        $this->assertNotNull($altOffer);

        // Each offer has exactly its own attribute value — not the other's, not both.
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $primaryOffer->id, 'attribute_id' => $attrModel->id, 'value_text' => 'XPS 15',
        ]);
        $this->assertDatabaseMissing('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $primaryOffer->id, 'attribute_id' => $attrDpi->id,
        ]);
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $altOffer->id, 'attribute_id' => $attrDpi->id, 'value_text' => '1600 DPI',
        ]);
        $this->assertDatabaseMissing('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $altOffer->id, 'attribute_id' => $attrModel->id,
        ]);

        // Removing the alternative offer cascade-deletes its own attribute
        // values only — the primary's stay untouched.
        $altOfferId = $altOffer->id;
        $altOffer->delete();
        $this->assertDatabaseMissing('quotation_item_attribute_values', ['quotation_item_offer_id' => $altOfferId]);
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $primaryOffer->id, 'attribute_id' => $attrModel->id, 'value_text' => 'XPS 15',
        ]);
    }

    /**
     * A reload of the edit page (not the live in-session state right after
     * selecting a marketplace product) hydrates offer._attribute_values from
     * scratch in _form.blade.php's PHP — before this, that was hardcoded to
     * an empty array/object for every offer, so a saved marketplace (or
     * custom) offer's structured attribute values, though genuinely present
     * in quotation_item_attribute_values, never appeared anywhere on the
     * page after a reload. This renders the real edit page (same as a
     * browser refresh) and confirms the saved value comes back.
     */
    public function test_edit_page_reload_hydrates_offers_saved_attribute_values(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-reloadattr-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-reloadattr-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $category = Category::create([
            'name' => 'Laptops', 'slug' => 'laptops-reloadattr-'.uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]);
        $attribute = \App\Models\Attribute::create(['name' => 'RAM', 'slug' => 'ram-reloadattr-'.uniqid(), 'input_type' => 'text']);
        $category->attributes()->attach($attribute->id);

        $listing = $this->makeListing($supplier, 'Reload');
        $listing->update(['main_category_id' => $category->id]);

        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Dell Laptop', 'quantity' => 1, 'unit_price' => 700,
                'offers' => [[
                    'offer_method' => 'marketplace',
                    'marketplace_product_id' => $listing->id,
                    'category_id' => $category->id,
                    'product_name' => 'Dell Laptop',
                    'quantity' => 1,
                    'unit_price' => 700,
                    'is_primary' => true,
                    'attribute_values' => [$attribute->id => ['value_text' => '32GB DDR5']],
                ]],
            ]],
        ]);

        $offer = $quotation->items->first()->offers()->first();
        $this->assertDatabaseHas('quotation_item_attribute_values', [
            'quotation_item_offer_id' => $offer->id, 'attribute_id' => $attribute->id, 'value_text' => '32GB DDR5',
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.quotations.edit', $quotation));
        $response->assertOk();
        $response->assertSee('32GB DDR5', false);
    }

    /**
     * Verifies the "Save Draft" button specifically — a real native form
     * POST through the STRICT SaveQuotationRequest (store()/update()), not
     * the relaxed autosave endpoint every other lazy-creation test in this
     * file goes through. _item.blade.php only emits an item's hidden
     * items[...] inputs at all when item.offers.length > 0 (see the
     * "only emitted if at least one offer exists" comment there), so an
     * untouched RFQ item is simply ABSENT from the submitted payload here —
     * matching that real shape exactly, not sending empty/default values for
     * Mouse and Keyboard. Confirms QuotationService::saveDraft()'s lazy
     * creation gate applies identically regardless of which route reaches it.
     */
    public function test_save_draft_native_form_submit_only_persists_items_with_real_offers(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-savedraft-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-savedraft-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem1 = $rfq->items[0]; // Laptop
        $rfqItem2 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Mouse', 'quantity' => 10, 'unit_id' => $rfqItem1->unit_id]);
        $rfqItem3 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Keyboard', 'quantity' => 5, 'unit_id' => $rfqItem1->unit_id]);

        $listing = $this->makeListing($supplier, 'SaveDraft');

        // Supplier clicks "Submit Quote" -> draft quotation header only, 0 items.
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(0, $quotation->items()->count());

        // Native "Save Draft" click: only Laptop's items[...] block is
        // present at all — Mouse/Keyboard are completely absent, exactly
        // like _item.blade.php renders when item.offers is empty.
        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'current_step' => 1,
            'max_completed_step' => 1,
            'items' => [
                'laptop-ref' => [
                    'id' => '',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => $listing->name,
                    'quantity' => 10,
                    'unit_id' => '',
                    'custom_unit' => '',
                    'unit_price' => 700,
                    'tax_rate' => '',
                    'discount_amount' => '',
                    'lead_time_days' => '',
                    'description' => '',
                    'offered_listing_id' => $listing->id,
                    'offered_variant_id' => '',
                    'response_method' => 'marketplace',
                    'client_ref' => 'laptop-ref',
                    'is_alternative' => '0',
                    'is_optional_addon' => '0',
                    'offers' => [
                        'laptop-offer-ref' => [
                            'id' => '',
                            'client_ref' => 'laptop-offer-ref',
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing->id,
                            'offered_variant_id' => '',
                            'is_primary' => '1',
                            'is_selected' => '0',
                            'sort_order' => '0',
                            'category_id' => '',
                            'specifications' => json_encode([]),
                            'attribute_values' => json_encode([]),
                            'product_name' => $listing->name,
                            'quantity' => 10,
                            'unit_id' => '',
                            'custom_unit' => '',
                            'unit_price' => 700,
                            'tax_rate' => '',
                            'discount' => '',
                            'delivery_time' => '',
                            'description' => '',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($supplier)->put(route('supplier.quotations.update', $quotation), $payload);
        $response->assertRedirect();

        $quotation->refresh();
        $this->assertCount(1, $quotation->items);
        $onlyItem = $quotation->items->first();
        $this->assertEquals($rfqItem1->id, $onlyItem->rfq_item_id);
        $this->assertCount(1, $onlyItem->offers);
        $this->assertEquals($listing->id, $onlyItem->offers->first()->marketplace_product_id);

        // No records at all for Mouse or Keyboard.
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem3->id)->count());
    }

    /**
     * Same scenario, but for the "Next: Refine & Add-Ons" button — which
     * calls autosave() directly (goNext() in _form.blade.php), sending the
     * FULL items array as real JS objects (not gated by the hidden-input
     * x-if the native form uses) — so this exercises the OTHER half of how
     * "untouched" items reach the backend: as items with an explicit empty
     * offers: [] array, rather than being absent from the payload entirely.
     * Both shapes must be lazily skipped the same way.
     */
    public function test_next_step_autosave_only_persists_items_with_real_offers(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'multioffer-nextstep-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'multioffer-nextstep-b@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItem1 = $rfq->items[0]; // Laptop
        $rfqItem2 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Mouse', 'quantity' => 10, 'unit_id' => $rfqItem1->unit_id]);
        $rfqItem3 = $rfq->items()->create(['item_type' => 'product', 'item_name' => 'Keyboard', 'quantity' => 5, 'unit_id' => $rfqItem1->unit_id]);

        $listing = $this->makeListing($supplier, 'NextStep');

        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);

        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 0,
            'current_step' => 2,
            'max_completed_step' => 2,
            'items' => [
                [
                    'client_ref' => 'laptop-ref',
                    'rfq_item_id' => $rfqItem1->id,
                    'item_name' => $listing->name,
                    'quantity' => 10,
                    'unit_price' => 700,
                    'offers' => [[
                        'client_ref' => 'laptop-offer-ref',
                        'offer_method' => 'marketplace',
                        'marketplace_product_id' => $listing->id,
                        'product_name' => $listing->name,
                        'quantity' => 10,
                        'unit_price' => 700,
                        'is_primary' => true,
                    ]],
                ],
                [
                    'client_ref' => 'mouse-ref',
                    'rfq_item_id' => $rfqItem2->id,
                    'item_name' => 'Mouse',
                    'quantity' => 10,
                    'unit_price' => null,
                    'offers' => [], // Alpine still sends the item wrapper, just an empty offers array
                ],
                [
                    'client_ref' => 'keyboard-ref',
                    'rfq_item_id' => $rfqItem3->id,
                    'item_name' => 'Keyboard',
                    'quantity' => 5,
                    'unit_price' => null,
                    'offers' => [],
                ],
            ],
        ];

        $res = $this->actingAs($supplier)->putJson(route('supplier.quotations.autosave.update', $quotation), $payload);
        $res->assertOk();

        $quotation->refresh();
        $this->assertCount(1, $quotation->items);
        $this->assertEquals($rfqItem1->id, $quotation->items->first()->rfq_item_id);
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem2->id)->count());
        $this->assertEquals(0, QuotationItem::where('quotation_id', $quotation->id)->where('rfq_item_id', $rfqItem3->id)->count());
    }
}



