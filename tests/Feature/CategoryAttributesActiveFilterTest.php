<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Database\Seeders\InputTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * A deactivated attribute (is_active=0) used to keep appearing on brand new
 * listings/RFQ items/quotations, because Category::attributesGroupedForForm()
 * filtered only the predefined VALUES by is_active, never the attribute
 * itself. Fixed by filtering the attribute query too, with one carve-out:
 * a caller editing a draft that already has a saved value for a since-
 * deactivated attribute can pass its id in $keepInactiveAttributeIds so the
 * field doesn't silently vanish and orphan already-entered data.
 */
class CategoryAttributesActiveFilterTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create(['name' => 'Electronics', 'slug' => 'electronics-active-filter-' . uniqid(), 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);
    }

    private function attachAttribute(Category $category, string $name, bool $isActive, string $inputType = 'text'): Attribute
    {
        $attribute = Attribute::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(), 'input_type' => $inputType, 'is_active' => $isActive]);
        $category->attributes()->attach($attribute->id, ['is_required' => false, 'is_filterable' => false, 'is_variant' => false, 'sort_order' => 0]);

        return $attribute;
    }

    public function test_a_deactivated_attribute_is_excluded_by_default(): void
    {
        $category = $this->category();
        $this->attachAttribute($category, 'Voltage', true);
        $this->attachAttribute($category, 'Legacy Field', false);

        $names = collect($category->attributesGroupedForForm()['groups'])
            ->flatMap(fn ($g) => $g['attributes'])
            ->pluck('name');

        $this->assertTrue($names->contains('Voltage'));
        $this->assertFalse($names->contains('Legacy Field'), 'A deactivated attribute must not appear on a new listing/RFQ/quotation form.');
    }

    public function test_a_deactivated_attribute_with_a_kept_id_still_appears(): void
    {
        $category = $this->category();
        $legacy = $this->attachAttribute($category, 'Legacy Field', false);

        $names = collect($category->attributesGroupedForForm([$legacy->id])['groups'])
            ->flatMap(fn ($g) => $g['attributes'])
            ->pluck('name');

        $this->assertTrue($names->contains('Legacy Field'), 'An attribute the caller already has a saved value for must still show, even if deactivated since.');
    }

    public function test_keeping_one_deactivated_attribute_does_not_reveal_a_different_deactivated_one(): void
    {
        $category = $this->category();
        $kept = $this->attachAttribute($category, 'Kept Legacy Field', false);
        $this->attachAttribute($category, 'Other Legacy Field', false);

        $names = collect($category->attributesGroupedForForm([$kept->id])['groups'])
            ->flatMap(fn ($g) => $g['attributes'])
            ->pluck('name');

        $this->assertTrue($names->contains('Kept Legacy Field'));
        $this->assertFalse($names->contains('Other Legacy Field'), 'Only the explicitly kept id should be exempted, not every inactive attribute.');
    }

    public function test_the_parent_fallback_path_also_respects_the_active_filter(): void
    {
        $parent = $this->category();
        $child = Category::create(['name' => 'Computers', 'slug' => 'computers-active-filter-' . uniqid(), 'parent_id' => $parent->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);
        $this->attachAttribute($parent, 'Voltage', true);
        $this->attachAttribute($parent, 'Legacy Field', false);

        // Child has no attributes of its own, so the method walks up to the parent.
        $names = collect($child->attributesGroupedForForm()['groups'])
            ->flatMap(fn ($g) => $g['attributes'])
            ->pluck('name');

        $this->assertTrue($names->contains('Voltage'));
        $this->assertFalse($names->contains('Legacy Field'));
    }

    /**
     * End-to-end check on the actual page the bug was reported on
     * (/supplier/catalog/listings/create): the JSON endpoint the wizard's
     * Alpine component fetches from must exclude inactive attributes for a
     * fresh request (new listing, no keep_attribute_ids), and include one
     * back when it's passed (simulating reopening a draft that already has
     * a value for it).
     */
    public function test_the_supplier_listing_wizards_category_attributes_endpoint_hides_inactive_by_default_and_keeps_when_asked(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(InputTypeSeeder::class);

        $supplier = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'supplier',
            'name'         => 'Supplier Active Filter Test',
            'email'        => 'supplier-active-filter@example.com',
            'phone'        => '+15550001234',
            'password'     => 'Password123!',
        ]);
        $account = $supplier->account;
        $supplier->markEmailAsVerified();
        $supplier->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->supplierCapability()->update(['status' => 'active']);
        $supplier->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $supplier->unsetRelation('roles')->unsetRelation('permissions');

        $plan = SubscriptionPlan::create([
            'name' => 'Free Plan', 'slug' => 'free-plan-active-filter-' . uniqid(), 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);
        $subscription = Subscription::create([
            'supplier_account_id' => $account->id, 'plan_id' => $plan->id, 'selected_by_user_id' => $supplier->id,
            'provider' => 'free', 'status' => 'pending',
        ]);
        $subscription->activate();

        $category = $this->category();
        $active = $this->attachAttribute($category, 'Voltage', true);
        $legacy = $this->attachAttribute($category, 'Legacy Field', false);

        $fresh = $this->actingAs($supplier->fresh())
            ->getJson(route('supplier.catalog.listings.category.attributes', $category));
        $fresh->assertOk();
        $freshNames = collect($fresh->json('groups'))->flatMap(fn ($g) => $g['attributes'])->pluck('name');
        $this->assertTrue($freshNames->contains('Voltage'));
        $this->assertFalse($freshNames->contains('Legacy Field'), 'A brand new listing must not show a deactivated attribute.');

        $withKeep = $this->actingAs($supplier->fresh())
            ->getJson(route('supplier.catalog.listings.category.attributes', $category) . '?keep_attribute_ids=' . $legacy->id);
        $withKeep->assertOk();
        $keptNames = collect($withKeep->json('groups'))->flatMap(fn ($g) => $g['attributes'])->pluck('name');
        $this->assertTrue($keptNames->contains('Legacy Field'), 'Reopening a draft with an existing value for the now-inactive attribute must still show it.');
    }
}
