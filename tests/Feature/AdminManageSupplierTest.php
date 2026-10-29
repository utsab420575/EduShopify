<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Listing;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Admin manages a supplier's Business Profile and Catalog listings from the
 * admin panel (/admin/suppliers/{id}/manage/...) with the supplier's own
 * controllers and views — see App\Support\SupplierWorkspace and
 * ManageSupplierAccount.
 */
class AdminManageSupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    /** Platform staff in the system account (not primary owner, so no Gate::before bypass). */
    private function makeStaff(string $email, string $role): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $user = User::create([
            'name' => 'Staff', 'email' => $email, 'phone' => '+1000000'.random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);
        AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $user->id, 'member_type' => 'member',
            'is_primary_owner' => false, 'status' => 'active', 'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $user->assignRole($role);

        return $user->fresh();
    }

    private function makeSupplier(string $email): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => 'supplier', 'name' => 'Managed Supplier',
            'email' => $email, 'phone' => '+1555000'.random_int(1000, 9999), 'password' => 'Password123!',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);
        $user->account->supplierCapability()->update(['status' => 'active']);

        $plan = SubscriptionPlan::create([
            'name' => 'Free', 'slug' => 'free-'.uniqid(), 'billing_type' => 'free', 'price' => 0,
            'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);
        Subscription::create([
            'supplier_account_id' => $user->account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $user->id, 'provider' => 'free', 'status' => 'pending',
        ])->activate();

        return $user->fresh();
    }

    private function makeListing(User $supplier, string $name): Listing
    {
        return Listing::create([
            'supplier_account_id' => $supplier->account->id, 'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-'.uniqid(), 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(),
            'approval_status' => 'draft', 'is_active' => true,
        ]);
    }

    public function test_admin_edits_a_supplier_profile_with_the_supplier_pages(): void
    {
        $admin = $this->makeStaff('manage-admin@example.com', 'admin');
        $supplier = $this->makeSupplier('managed-supplier@example.com');
        $accountId = $supplier->account->id;

        $page = $this->actingAs($admin)->get(route('admin.supplier-manage.company.profile', ['supplierAccount' => $accountId]));
        $page->assertOk()
            ->assertSee('Managing supplier:')
            // Forms post back into the admin panel, not to /supplier/...
            ->assertSee("/admin/suppliers/{$accountId}/manage/company/profile/company", false)
            ->assertDontSee('"'.url('/supplier/company/profile/company').'"', false);

        $this->actingAs($admin)->put(route('admin.supplier-manage.company.profile.contact.update', ['supplierAccount' => $accountId]), [
            'contact_person' => 'Edited By Admin',
            'contact_email' => 'contact@managed.test',
        ])->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.supplier-manage.company.profile', ['supplierAccount' => $accountId, 'section' => 'contact']));

        $this->assertSame('Edited By Admin', $supplier->account->supplierProfile->fresh()->contact_person);
    }

    public function test_admin_manages_a_supplier_products(): void
    {
        $admin = $this->makeStaff('manage-admin2@example.com', 'admin_staff');
        $supplier = $this->makeSupplier('managed-supplier2@example.com');
        $accountId = $supplier->account->id;
        $listing = $this->makeListing($supplier, 'Managed Microscope');

        $this->actingAs($admin)->get(route('admin.supplier-manage.catalog.listings.index', ['supplierAccount' => $accountId]))
            ->assertOk()->assertSee('Managed Microscope')->assertSee('Managing supplier:');
        $this->actingAs($admin)->get(route('admin.supplier-manage.catalog.listings.create', ['supplierAccount' => $accountId]))
            ->assertOk();

        $this->actingAs($admin)->delete(route('admin.supplier-manage.catalog.listings.destroy', ['supplierAccount' => $accountId, 'listing' => $listing->id]))
            ->assertRedirect(route('admin.supplier-manage.catalog.listings.index', ['supplierAccount' => $accountId]));
        $this->assertSoftDeleted($listing);
    }

    /**
     * The product wizard's JavaScript (step saves, media, preview) must call
     * the admin-panel URLs, never /supplier/... — those are behind
     * RequireSupplierCapability and 403 for an admin ("Your Supplier
     * capability is not active").
     */
    public function test_admin_product_wizard_uses_admin_urls_and_saves_for_the_supplier(): void
    {
        $admin = $this->makeStaff('manage-wizard@example.com', 'admin');
        $supplier = $this->makeSupplier('managed-wizard@example.com');
        $accountId = $supplier->account->id;
        $listing = $this->makeListing($supplier, 'Wizard Microscope');
        $adminBase = route('admin.supplier-manage.catalog.listings.index', ['supplierAccount' => $accountId]);
        $supplierBase = url('supplier/catalog/listings');

        foreach ([
            route('admin.supplier-manage.catalog.listings.create', ['supplierAccount' => $accountId]),
            route('admin.supplier-manage.catalog.listings.edit', ['supplierAccount' => $accountId, 'listing' => $listing->id]),
        ] as $page) {
            $html = $this->actingAs($admin)->get($page)->assertOk()->getContent();
            $this->assertStringContainsString($adminBase.'/', $html);
            $this->assertStringNotContainsString($supplierBase, $html, "Wizard on {$page} still calls a /supplier/ URL.");
        }

        // Step 1 saved by the admin creates the product on the supplier's account.
        $type = \App\Models\ListingType::firstOrCreate(['code' => 'product'], ['name' => 'Product', 'is_active' => true]);
        $this->actingAs($admin)->postJson(route('admin.supplier-manage.catalog.listings.wizard.step1', ['supplierAccount' => $accountId]), [
            'listing_type_id' => $type->id,
            'name' => 'Admin Added Projector',
        ])->assertOk();

        $created = Listing::where('name', 'Admin Added Projector')->firstOrFail();
        $this->assertSame($accountId, $created->supplier_account_id);
        $this->assertSame($admin->id, $created->created_by_user_id);
    }

    public function test_admin_cannot_reach_another_suppliers_listing_through_this_supplier(): void
    {
        $admin = $this->makeStaff('manage-admin3@example.com', 'admin');
        $supplierA = $this->makeSupplier('managed-a@example.com');
        $supplierB = $this->makeSupplier('managed-b@example.com');
        $listingOfB = $this->makeListing($supplierB, 'Belongs To B');

        $this->actingAs($admin)->delete(route('admin.supplier-manage.catalog.listings.destroy', ['supplierAccount' => $supplierA->account->id, 'listing' => $listingOfB->id]))
            ->assertForbidden();
        $this->assertNotSoftDeleted($listingOfB);
    }

    public function test_access_requires_permission_and_a_supplier_account(): void
    {
        $moderator = $this->makeStaff('manage-moderator@example.com', 'moderator');
        $admin = $this->makeStaff('manage-admin4@example.com', 'admin');
        $supplier = $this->makeSupplier('managed-supplier4@example.com');

        // Moderator lacks platform.suppliers.manage.
        $this->actingAs($moderator)->get(route('admin.supplier-manage.company.profile', ['supplierAccount' => $supplier->account->id]))
            ->assertForbidden();

        // The system account is not a supplier.
        $systemId = Account::where('account_number', 'SYSTEM')->value('id');
        $this->actingAs($admin)->get(route('admin.supplier-manage.company.profile', ['supplierAccount' => $systemId]))
            ->assertNotFound();

        // A supplier can't use the admin entry point at all.
        $this->actingAs($supplier)->get(route('admin.supplier-manage.company.profile', ['supplierAccount' => $supplier->account->id]))
            ->assertForbidden();
    }

    public function test_supplier_own_pages_still_use_supplier_urls(): void
    {
        $supplier = $this->makeSupplier('managed-supplier5@example.com');
        $supplier->activateTeamContext();

        $this->actingAs($supplier)->get(route('supplier.company.profile'))
            ->assertOk()
            ->assertSee(url('/supplier/company/profile/company'), false)
            ->assertDontSee('Managing supplier:')
            ->assertDontSee('/manage/company/profile', false);
    }
}
