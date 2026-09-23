<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Quotation;
use App\Models\QuotationDeliveryAddress;
use App\Models\Rfq;
use App\Models\RfqDeliveryAddress;
use App\Models\State;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the "Detail & Delivery" step 2 additions: quotations.title/description,
 * and quotation_delivery_addresses — seeded once from the RFQ's own address(es)
 * when the quotation is first created, then fully independent from that point on.
 */
class SupplierQuotationDetailDeliveryTest extends TestCase
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
            'account_type' => 'individual', 'capability' => $capability,
            'name' => ucfirst($capability).' Detail Delivery User', 'email' => $email,
            'phone' => '+1555000'.random_int(1000, 9999), 'password' => 'Password123!',
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
                'name' => 'Free Plan', 'slug' => 'free-plan-detaildelivery-'.uniqid(), 'billing_type' => 'free',
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

    private function makeGeography(): array
    {
        $country = Country::create(['name' => 'Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'is_active' => true]);
        $stateDhaka = State::create(['country_id' => $country->id, 'name' => 'Dhaka Division', 'is_active' => true]);
        $stateChittagong = State::create(['country_id' => $country->id, 'name' => 'Chittagong Division', 'is_active' => true]);
        $cityDhaka = City::create(['country_id' => $country->id, 'state_id' => $stateDhaka->id, 'name' => 'Dhaka', 'is_active' => true]);
        $cityChittagong = City::create(['country_id' => $country->id, 'state_id' => $stateChittagong->id, 'name' => 'Chittagong', 'is_active' => true]);

        return compact('country', 'stateDhaka', 'stateChittagong', 'cityDhaka', 'cityChittagong');
    }

    /**
     * The exact example from the request: RFQ's primary address is "Dhaka
     * Warehouse" — creating a quotation copies it in, and the supplier can
     * then edit it to "Chittagong Warehouse" without touching the RFQ.
     */
    public function test_quotation_delivery_address_is_copied_from_rfq_on_creation_and_editable_independently(): void
    {
        $this->seedBase();
        $geo = $this->makeGeography();
        $supplier = $this->makeActiveAccount('supplier', 'detaildelivery-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'detaildelivery-b@example.com');

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Detail Delivery RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'delivery_country_id' => $geo['country']->id,
            'delivery_state_id' => $geo['stateDhaka']->id,
            'delivery_city_id' => $geo['cityDhaka']->id,
            'delivery_address' => 'Dhaka Warehouse',
            'items' => [['item_type' => 'product', 'item_name' => 'Laptop', 'quantity' => 10]],
        ]);
        $rfq = app(RfqService::class)->publish($rfq);

        // Supplier clicks "Submit Quote" — this is the exact call that
        // creates the quotation row (QuotationController::create()).
        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertNotNull($quotation);

        // Copied in automatically.
        $this->assertCount(1, $quotation->deliveryAddresses);
        $copied = $quotation->deliveryAddresses->first();
        $this->assertEquals('Dhaka Warehouse', $copied->address);
        $this->assertEquals($geo['cityDhaka']->id, $copied->city_id);
        $this->assertEquals(0, $copied->sort_order);

        // Supplier edits it via the Step 2 autosave.
        $service = app(QuotationService::class);
        $updated = $service->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [],
            'delivery_addresses' => [[
                'country_id' => $geo['country']->id,
                'state_id' => $geo['stateChittagong']->id,
                'city_id' => $geo['cityChittagong']->id,
                'address' => 'Chittagong Warehouse',
            ]],
        ], $quotation);

        $this->assertCount(1, $updated->deliveryAddresses()->get());
        $edited = $updated->deliveryAddresses()->first();
        $this->assertEquals('Chittagong Warehouse', $edited->address);
        $this->assertEquals($geo['cityChittagong']->id, $edited->city_id);

        // The buyer's own RFQ address is completely untouched.
        $rfq->refresh();
        $this->assertEquals('Dhaka Warehouse', $rfq->delivery_address);
        $this->assertEquals($geo['cityDhaka']->id, $rfq->delivery_city_id);
    }

    public function test_additional_rfq_delivery_addresses_are_also_copied(): void
    {
        $this->seedBase();
        $geo = $this->makeGeography();
        $supplier = $this->makeActiveAccount('supplier', 'detaildelivery2-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'detaildelivery2-b@example.com');

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Multi Address RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'delivery_country_id' => $geo['country']->id,
            'delivery_city_id' => $geo['cityDhaka']->id,
            'delivery_address' => 'Dhaka Warehouse',
            'additional_addresses' => [[
                'country_id' => $geo['country']->id,
                'city_id' => $geo['cityChittagong']->id,
                'address' => 'Chittagong Branch',
            ]],
            'items' => [['item_type' => 'product', 'item_name' => 'Laptop', 'quantity' => 10]],
        ]);
        $rfq = app(RfqService::class)->publish($rfq);
        $this->assertCount(1, $rfq->deliveryAddresses);

        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();

        $this->assertCount(2, $quotation->deliveryAddresses);
        $this->assertEquals(
            ['Dhaka Warehouse', 'Chittagong Branch'],
            $quotation->deliveryAddresses->sortBy('sort_order')->pluck('address')->all()
        );

        // Original rfq_delivery_addresses row is untouched.
        $this->assertCount(1, RfqDeliveryAddress::where('rfq_id', $rfq->id)->get());
    }

    public function test_title_and_description_are_saved_via_autosave(): void
    {
        $this->seedBase();
        $supplier = $this->makeActiveAccount('supplier', 'detaildelivery3-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'detaildelivery3-b@example.com');
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Title Desc RFQ', 'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items' => [['item_type' => 'product', 'item_name' => 'Laptop', 'quantity' => 10]],
        ]);
        $rfq = app(RfqService::class)->publish($rfq);

        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();

        $res = $this->actingAs($supplier)->putJson(route('supplier.quotations.autosave.update', $quotation), [
            'title' => 'Laptop Supply Quotation',
            'description' => 'Complete laptop supply with warranty and support.',
            'items' => [],
        ]);
        $res->assertOk();

        $quotation->refresh();
        $this->assertEquals('Laptop Supply Quotation', $quotation->title);
        $this->assertEquals('Complete laptop supply with warranty and support.', $quotation->description);
    }

    /**
     * A saveDraft() call that never mentions delivery_addresses at all (e.g.
     * QuotationController::create()'s own internal header-only save) must
     * not wipe out the addresses copyDeliveryAddressesFromRfq() just seeded —
     * only an explicit key present in the payload should ever resync them.
     */
    public function test_saving_items_only_does_not_wipe_copied_delivery_addresses(): void
    {
        $this->seedBase();
        $geo = $this->makeGeography();
        $supplier = $this->makeActiveAccount('supplier', 'detaildelivery4-s@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'detaildelivery4-b@example.com');
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'No Wipe RFQ', 'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'delivery_country_id' => $geo['country']->id,
            'delivery_city_id' => $geo['cityDhaka']->id,
            'delivery_address' => 'Dhaka Warehouse',
            'items' => [['item_type' => 'product', 'item_name' => 'Laptop', 'quantity' => 10]],
        ]);
        $rfq = app(RfqService::class)->publish($rfq);

        $this->actingAs($supplier)->get(route('supplier.quotations.create', $rfq));
        $quotation = Quotation::where('rfq_id', $rfq->id)->where('supplier_account_id', $supplier->account->id)->first();
        $this->assertCount(1, $quotation->deliveryAddresses);

        // Save again, WITHOUT the delivery_addresses key at all.
        app(QuotationService::class)->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [],
        ], $quotation);

        $this->assertCount(1, $quotation->fresh()->deliveryAddresses);
    }
}
