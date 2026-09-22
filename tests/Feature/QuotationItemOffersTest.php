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
use App\Models\RfqItem;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationItemOffersTest extends TestCase
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
        $user = app(\App\Services\AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability' => $capability,
            'name' => ucfirst($capability).' Test User',
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
            $plan = \App\Models\SubscriptionPlan::create([
                'name' => 'Free Plan',
                'slug' => 'free-plan-'.uniqid(),
                'billing_type' => 'free',
                'price' => 0,
                'currency_code' => 'USD',
                'is_free' => true,
                'is_active' => true,
                'max_active_listings' => 10,
                'max_monthly_quotations' => 50,
                'rfq_delay_minutes' => 0,
            ]);
            $subscription = \App\Models\Subscription::create([
                'supplier_account_id' => $account->id,
                'plan_id' => $plan->id,
                'selected_by_user_id' => $user->id,
                'provider' => 'free',
                'status' => 'pending',
            ]);
            $subscription->activate();
        }

        return $user->fresh();
    }

    public function test_save_draft_creates_single_quotation_item_with_multiple_offers()
    {
        $this->seedBase();
        $supplierUser = $this->makeActiveAccount('supplier', 'supplier@test.com');
        $supplier = $supplierUser->account;
        $buyerUser = $this->makeActiveAccount('buyer', 'buyer@test.com');
        $buyer = $buyerUser->account;

        $category = Category::create([
            'name' => 'Computers & Laptops',
            'slug' => 'computers-laptops',
            'type' => 'product',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-2026-TEST',
            'buyer_account_id' => $buyer->id,
            'created_by_user_id' => $buyerUser->id,
            'title' => 'Need 100 Desktop Computers',
            'status' => 'published',
            'allow_alternative_products' => true,
            'allow_partial_quotation' => true,
            'current_version_no' => 1,
            'currency_code' => 'USD',
        ]);

        $rfqItem = RfqItem::create([
            'rfq_id' => $rfq->id,
            'item_type' => 'product',
            'item_name' => 'I need computer',
            'category_id' => $category->id,
            'quantity' => 100,
            'sort_order' => 0,
        ]);

        $listing1 = Listing::create([
            'supplier_account_id' => $supplier->id,
            'created_by_user_id' => $supplierUser->id,
            'listing_number' => 'LST-TEST-1',
            'main_category_id' => $category->id,
            'name' => 'Dell OptiPlex 7090 Desktop',
            'slug' => 'dell-optiplex-7090',
            'listing_type' => 'product',
            'approval_status' => 'approved',
            'base_price' => 850.00,
        ]);

        $listing2 = Listing::create([
            'supplier_account_id' => $supplier->id,
            'created_by_user_id' => $supplierUser->id,
            'listing_number' => 'LST-TEST-2',
            'main_category_id' => $category->id,
            'name' => 'HP ProDesk 400 G7',
            'slug' => 'hp-prodesk-400-g7',
            'listing_type' => 'product',
            'approval_status' => 'approved',
            'base_price' => 750.00,
        ]);

        $service = app(QuotationService::class);

        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 200.00,
            'items' => [
                [
                    'id' => null,
                    'rfq_item_id' => $rfqItem->id,
                    'item_name' => 'Dell OptiPlex 7090 Desktop',
                    'quantity' => 100,
                    'unit_price' => 850.00,
                    'tax_rate' => 5.0,
                    'discount_amount' => 100.00,
                    'lead_time_days' => 7,
                    'is_primary_offer' => true,
                    'offers' => [
                        [
                            'id' => null,
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing1->id,
                            'product_name' => 'Dell OptiPlex 7090 Desktop',
                            'category_id' => $category->id,
                            'description' => 'Primary Offer (Dell OptiPlex)',
                            'specifications' => [
                                ['name' => 'RAM', 'value' => '16GB DDR4'],
                                ['name' => 'Storage', 'value' => '512GB SSD'],
                            ],
                            'quantity' => 100,
                            'unit_price' => 850.00,
                            'tax_rate' => 5.0,
                            'discount' => 100.00,
                            'delivery_time' => 7,
                            'is_primary' => true,
                            'is_selected' => false,
                            'sort_order' => 0,
                        ],
                        [
                            'id' => null,
                            'offer_method' => 'marketplace',
                            'marketplace_product_id' => $listing2->id,
                            'product_name' => 'HP ProDesk 400 G7',
                            'category_id' => $category->id,
                            'description' => 'Alternative Offer (HP ProDesk)',
                            'specifications' => [
                                ['name' => 'RAM', 'value' => '16GB DDR4'],
                                ['name' => 'Storage', 'value' => '256GB SSD'],
                            ],
                            'quantity' => 100,
                            'unit_price' => 750.00,
                            'tax_rate' => 5.0,
                            'discount' => 50.00,
                            'delivery_time' => 10,
                            'is_primary' => false,
                            'is_selected' => false,
                            'sort_order' => 1,
                        ],
                    ],
                ]
            ],
        ];

        $quotation = $service->saveDraft($rfq, $supplier, $supplierUser, $payload);

        // 1. Assert exactly 1 quotation_item was created (Product Response layer)
        $this->assertEquals(1, $quotation->items()->count());

        $quotationItem = $quotation->items()->first();
        $this->assertEquals($rfqItem->id, $quotationItem->rfq_item_id);
        $this->assertEquals('Dell OptiPlex 7090 Desktop', $quotationItem->item_name);
        $this->assertEquals(850.00, (float) $quotationItem->unit_price);

        // 2. Assert exactly 2 quotation_item_offers were created under it
        $this->assertEquals(2, $quotationItem->offers()->count());

        $primaryOffer = $quotationItem->primaryOffer;
        $this->assertNotNull($primaryOffer);
        $this->assertTrue((bool) $primaryOffer->is_primary);
        $this->assertFalse((bool) $primaryOffer->is_selected);
        $this->assertEquals(0, $primaryOffer->sort_order);
        $this->assertEquals($listing1->id, $primaryOffer->marketplace_product_id);
        $this->assertEquals('Dell OptiPlex 7090 Desktop', $primaryOffer->product_name);
        $this->assertEquals(850.00, (float) $primaryOffer->unit_price);
        $this->assertIsArray($primaryOffer->specifications);
        $this->assertCount(2, $primaryOffer->specifications);

        $altOffer = $quotationItem->offers()->where('is_primary', false)->first();
        $this->assertNotNull($altOffer);
        $this->assertFalse((bool) $altOffer->is_primary);
        $this->assertFalse((bool) $altOffer->is_selected);
        $this->assertEquals(1, $altOffer->sort_order);
        $this->assertEquals($listing2->id, $altOffer->marketplace_product_id);
        $this->assertEquals('HP ProDesk 400 G7', $altOffer->product_name);
        $this->assertEquals(750.00, (float) $altOffer->unit_price);

        // 3. Assert Grand Total accounts ONLY for the Primary Offer:
        // Subtotal = 100 * 850 = 85,000
        // Discount = 100
        // Taxable = 84,900 * 5% = 4,245
        // Shipping = 200
        // Grand Total = 84,900 + 4,245 + 200 = 89,345
        $this->assertEquals(89345.00, (float) $quotation->grand_total);
    }

    public function test_supplier_can_access_product_selector_with_matching_products()
    {
        $this->seedBase();
        $supplierUser = $this->makeActiveAccount('supplier', 'supplier2@test.com');
        $supplier = $supplierUser->account;
        $buyerUser = $this->makeActiveAccount('buyer', 'buyer2@test.com');
        $buyer = $buyerUser->account;

        $category = Category::create([
            'name' => 'Laptops & Workstations',
            'slug' => 'laptops-workstations',
            'type' => 'product',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-MATCH-TEST',
            'buyer_account_id' => $buyer->id,
            'created_by_user_id' => $buyerUser->id,
            'title' => 'Need Dell Laptop',
            'status' => 'open',
            'quotation_deadline' => now()->addDays(7),
            'allow_alternative_products' => true,
            'current_version_no' => 1,
            'currency_code' => 'USD',
        ]);

        $rfqItem = RfqItem::create([
            'rfq_id' => $rfq->id,
            'item_type' => 'product',
            'item_name' => 'Dell Laptop',
            'category_id' => $category->id,
            'quantity' => 10,
            'sort_order' => 0,
        ]);

        $matchingListing = Listing::create([
            'supplier_account_id' => $supplier->id,
            'created_by_user_id' => $supplierUser->id,
            'listing_number' => 'LST-MATCH-1',
            'main_category_id' => $category->id,
            'name' => 'Dell Laptop Latitude 5420',
            'slug' => 'dell-laptop-latitude-5420',
            'listing_type' => 'product',
            'approval_status' => 'approved',
            'base_price' => 1100.00,
        ]);

        $response = $this->actingAs($supplierUser)
            ->get(route('supplier.select-products-for-quotation', [
                'rfq_item_id' => $rfqItem->id,
                'rfq_id' => $rfq->id,
                'return_url' => route('supplier.quotations.create', $rfq->id),
            ]));

        $response->assertStatus(200);
        $response->assertSee('Dell Laptop Latitude 5420');
        $response->assertSee('Category matched');
    }

    public function test_autosave_persists_quotation_item_offers()
    {
        $this->seedBase();
        $supplierUser = $this->makeActiveAccount('supplier', 'supplier3@test.com');
        $supplier = $supplierUser->account;
        $buyerUser = $this->makeActiveAccount('buyer', 'buyer3@test.com');
        $buyer = $buyerUser->account;

        $category = Category::create([
            'name' => 'Server Equipment',
            'slug' => 'server-equipment',
            'type' => 'product',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-AUTOSAVE-TEST',
            'buyer_account_id' => $buyer->id,
            'created_by_user_id' => $buyerUser->id,
            'title' => 'Server Hardware RFQ',
            'status' => 'open',
            'quotation_deadline' => now()->addDays(7),
            'allow_alternative_products' => true,
            'current_version_no' => 1,
            'currency_code' => 'USD',
        ]);

        $rfqItem = RfqItem::create([
            'rfq_id' => $rfq->id,
            'item_type' => 'product',
            'item_name' => 'Rackmount Server',
            'category_id' => $category->id,
            'quantity' => 2,
            'sort_order' => 0,
        ]);

        $payload = [
            'currency_code' => 'USD',
            'shipping_charge' => 50.00,
            'items' => [
                [
                    'id' => null,
                    'rfq_item_id' => $rfqItem->id,
                    'item_name' => 'Dell PowerEdge R740',
                    'quantity' => 2,
                    'unit_price' => 3000.00,
                    'tax_rate' => 0.0,
                    'discount_amount' => 0.0,
                    'is_primary_offer' => true,
                    'offers' => [
                        [
                            'id' => null,
                            'offer_method' => 'custom',
                            'product_name' => 'Dell PowerEdge R740',
                            'category_id' => $category->id,
                            'description' => 'Custom server config',
                            'specifications' => [
                                ['name' => 'CPU', 'value' => 'Dual Xeon Gold'],
                                ['name' => 'RAM', 'value' => '64GB DDR4 ECC'],
                            ],
                            'quantity' => 2,
                            'unit_price' => 3000.00,
                            'tax_rate' => 0.0,
                            'discount' => 0.0,
                            'is_primary' => true,
                            'is_selected' => false,
                            'sort_order' => 0,
                        ],
                        [
                            'id' => null,
                            'offer_method' => 'custom',
                            'product_name' => 'HPE ProLiant DL380 Gen10',
                            'category_id' => $category->id,
                            'description' => 'Alternative server config',
                            'specifications' => [
                                ['name' => 'CPU', 'value' => 'Dual Xeon Silver'],
                                ['name' => 'RAM', 'value' => '32GB DDR4 ECC'],
                            ],
                            'quantity' => 2,
                            'unit_price' => 2800.00,
                            'tax_rate' => 0.0,
                            'discount' => 0.0,
                            'is_primary' => false,
                            'is_selected' => false,
                            'sort_order' => 1,
                        ],
                    ],
                ]
            ],
        ];

        $response = $this->actingAs($supplierUser)
            ->postJson(route('supplier.quotations.autosave.create', $rfq->id), $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'quotation_number']);

        $savedDraft = Quotation::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $supplier->id)
            ->where('status', 'draft')
            ->first();

        $this->assertNotNull($savedDraft);
        $this->assertEquals(1, $savedDraft->items()->count());

        $item = $savedDraft->items()->first();
        $this->assertEquals(2, $item->offers()->count());

        $primary = $item->primaryOffer;
        $this->assertNotNull($primary);
        $this->assertEquals('Dell PowerEdge R740', $primary->product_name);
        $this->assertEquals(3000.00, (float) $primary->unit_price);
        $this->assertTrue((bool) $primary->is_primary);

        $alt = $item->offers()->where('is_primary', false)->first();
        $this->assertNotNull($alt);
        $this->assertEquals('HPE ProLiant DL380 Gen10', $alt->product_name);
        $this->assertEquals(2800.00, (float) $alt->unit_price);

        // Grand total should only reflect primary offer (2 * 3000 = 6000 + 50 shipping = 6050)
        $this->assertEquals(6050.00, (float) $savedDraft->grand_total);
    }
}
