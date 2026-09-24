<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the buyer Compare Quotations redesign: quotation_items.response_method
 * / is_alternative / is_optional_addon are no longer used in comparison
 * logic (quotation_item_offers.offer_method is the source of truth), the
 * comparison response is RFQ-based and nests offers under each Product
 * Response, and "lowest total"/"can_award" stay indicators — never an
 * automatic winner.
 */
class QuotationCompareCardRedesignTest extends TestCase
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
            'name' => ucfirst($capability).' Compare Test User',
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
                'name' => 'Free Plan', 'slug' => 'free-plan-compare-'.uniqid(), 'billing_type' => 'free',
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
            'title' => 'Compare RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'allow_alternative_products' => true,
            'items' => [
                [
                    'item_type' => 'product', 'item_name' => 'I need computers', 'quantity' => 10,
                    'specs' => [['name' => '__is_requirement', 'value' => '1']],
                ],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    private function quoteAsSupplier(Rfq $rfq, User $supplier, int $rfqItemId, array $offers): \App\Models\Quotation
    {
        $service = app(QuotationService::class);
        $quotation = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [[
                'rfq_item_id' => $rfqItemId, 'item_name' => 'Model X', 'quantity' => 10, 'unit_price' => $offers[0]['unit_price'],
                'offers' => $offers,
            ]],
        ]);

        return $service->submitDraft($quotation);
    }

    public function test_compare_data_response_has_no_response_method_is_alternative_or_addons_fields(): void
    {
        $this->seedBase();
        $supplierA = $this->makeActiveAccount('supplier', 'compare-sA@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'compare-sB@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'compare-b1@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $this->quoteAsSupplier($rfq, $supplierA, $rfqItemId, [
            ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
            ['offer_method' => 'custom', 'product_name' => 'HP Model X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => false],
        ]);
        $quotationB = $this->quoteAsSupplier($rfq, $supplierB, $rfqItemId, [
            ['offer_method' => 'marketplace', 'product_name' => 'Asus Model X', 'quantity' => 10, 'unit_price' => 650, 'is_primary' => true],
        ]);

        $ids = $rfq->quotations()->pluck('id')->all();
        $response = $this->actingAs($buyer)->postJson(
            route('buyer.quotations.compare.data', $rfq),
            ['quotation_ids' => $ids]
        );

        $response->assertOk();
        $response->assertJsonMissing(['addons' => []]);
        $this->assertArrayNotHasKey('addons', $response->json());

        $productResponse = $response->json('items.0.offers.'.$quotationB->id.'.0');
        $this->assertArrayNotHasKey('response_method', $productResponse);
        $this->assertArrayNotHasKey('is_alternative', $productResponse);
        $this->assertArrayNotHasKey('offer_type', $productResponse);

        // Source of truth for method is the nested offer, not the parent.
        $this->assertEquals('marketplace', $productResponse['offers'][0]['offer_method']);

        // RFQ-item type classification present.
        $this->assertEquals('quotation_only', $response->json('items.0.item_type'));

        // Supplier rating surfaced per spec §5.2.
        $summaryA = collect($response->json('summary'))->firstWhere('quotation_number', '!=', $quotationB->quotation_number);
        $this->assertArrayHasKey('supplier_rating', $summaryA);
    }

    public function test_lowest_grand_total_is_only_a_badge_not_an_automatic_award(): void
    {
        $this->seedBase();
        $supplierA = $this->makeActiveAccount('supplier', 'compare-sC@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'compare-sD@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'compare-b2@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $this->quoteAsSupplier($rfq, $supplierA, $rfqItemId, [
            ['offer_method' => 'marketplace', 'product_name' => 'Cheap X', 'quantity' => 10, 'unit_price' => 500, 'is_primary' => true],
        ]);
        $this->quoteAsSupplier($rfq, $supplierB, $rfqItemId, [
            ['offer_method' => 'marketplace', 'product_name' => 'Pricier X', 'quantity' => 10, 'unit_price' => 900, 'is_primary' => true],
        ]);

        $ids = $rfq->quotations()->pluck('id')->all();
        $response = $this->actingAs($buyer)->postJson(
            route('buyer.quotations.compare.data', $rfq),
            ['quotation_ids' => $ids]
        );

        $response->assertOk();
        $lowestId = $response->json('commercial.badges.lowest_grand_total_id');
        $this->assertNotNull($lowestId);

        // No award exists anywhere — the badge is purely informational.
        $this->assertEquals(0, Award::count());
        // Both quotations remain in their submitted state — nothing was
        // auto-awarded/auto-decided based on price.
        $this->assertEquals(2, $rfq->quotations()->where('status', 'submitted')->count());
    }

    public function test_awarding_still_happens_at_the_whole_quotation_level_only(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'compare-sE@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'compare-b3@example.com');
        $rfq = $this->makeOpenRfq($buyer);
        $rfqItemId = $rfq->items[0]->id;

        $quotation = $this->quoteAsSupplier($rfq, $supplier, $rfqItemId, [
            ['offer_method' => 'marketplace', 'product_name' => 'Dell Model X', 'quantity' => 10, 'unit_price' => 700, 'is_primary' => true],
        ]);

        $award = app(\App\Services\AwardService::class)->create($quotation, $buyer);

        // One award record for the whole quotation — no per-item/per-offer
        // award concept exists (single-award version; partial/multi-award
        // is future scope).
        $this->assertEquals($quotation->id, $award->quotation_id);
        $this->assertEquals(1, Award::where('quotation_id', $quotation->id)->count());
    }
}
