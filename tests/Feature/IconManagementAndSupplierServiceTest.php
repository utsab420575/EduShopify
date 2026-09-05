<?php

namespace Tests\Feature;

use App\Models\Icon;
use App\Models\IconLibrary;
use App\Models\Service;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IconManagementAndSupplierServiceTest extends TestCase
{
    use RefreshDatabase;
    private function makePlatformAdmin(): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);

        $systemAccount = \App\Models\Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name'              => 'Test Admin',
            'email'             => 'admin_icon_test_' . uniqid() . '@example.com',
            'phone'             => '+10000000001',
            'password'          => bcrypt('Password123!'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);

        \App\Models\AccountMember::create([
            'account_id'       => $systemAccount->id,
            'user_id'          => $admin->id,
            'member_type'      => 'owner',
            'is_primary_owner' => true,
            'status'           => 'active',
            'joined_at'        => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    private function makeSupplierUser(): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'supplier',
            'name'         => 'Service Test Supplier',
            'email'        => 'service_supplier_' . uniqid() . '@example.com',
            'phone'        => '+1555000' . random_int(1000, 9999),
            'password'     => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->supplierCapability()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        // The Business Profile routes require an active subscription
        // (EnsureSupplierHasPlan) — without one, requests to
        // supplier.company.profile.services.* redirect to supplier.pricing
        // instead of reaching the controller.
        $plan = \App\Models\SubscriptionPlan::create([
            'name' => 'Free Plan', 'slug' => 'free-plan-' . uniqid(),
            'billing_type' => 'free', 'price' => 0, 'currency_code' => 'USD',
            'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);
        $subscription = \App\Models\Subscription::create([
            'supplier_account_id' => $account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $user->id, 'provider' => 'free', 'status' => 'pending',
        ]);
        $subscription->activate();

        return $user->fresh();
    }

    public function test_icon_libraries_admin_crud(): void
    {
        $admin = $this->makePlatformAdmin();

        // 1. Index
        $response = $this->actingAs($admin)->get(route('admin.catalog.icon-libraries.index'));
        $response->assertOk();
        $response->assertSee('Icon Libraries');

        // 2. Create
        $response = $this->actingAs($admin)->get(route('admin.catalog.icon-libraries.create'));
        $response->assertOk();

        // 3. Store
        $slug = 'test-lib-' . uniqid();
        $response = $this->actingAs($admin)->post(route('admin.catalog.icon-libraries.store'), [
            'name' => 'Test Pack',
            'slug' => $slug,
            'type' => 'CDN',
            'cdn_url' => 'https://example.com/icons.css',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.catalog.icon-libraries.index'));

        $library = IconLibrary::where('slug', $slug)->firstOrFail();
        $this->assertSame('Test Pack', $library->name);
        $this->assertTrue($library->is_active);

        // 4. Edit
        $response = $this->actingAs($admin)->get(route('admin.catalog.icon-libraries.edit', $library));
        $response->assertOk();

        // 5. Update
        $response = $this->actingAs($admin)->put(route('admin.catalog.icon-libraries.update', $library), [
            'name' => 'Test Pack Updated',
            'slug' => $slug,
            'type' => 'Local',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.catalog.icon-libraries.index'));
        $this->assertSame('Test Pack Updated', $library->fresh()->name);

        // 6. Toggle Active
        $this->actingAs($admin)->post(route('admin.catalog.icon-libraries.toggle-active', $library));
        $this->assertFalse($library->fresh()->is_active);

        // 7. Destroy
        $this->actingAs($admin)->delete(route('admin.catalog.icon-libraries.destroy', $library));
        $this->assertNull(IconLibrary::find($library->id));
    }

    public function test_icons_admin_crud_and_api_search(): void
    {
        $admin = $this->makePlatformAdmin();
        $library = IconLibrary::firstOrCreate(
            ['slug' => 'test-icons-lib'],
            ['name' => 'Test Lib', 'type' => 'Local', 'is_active' => true]
        );

        // 1. Index
        $response = $this->actingAs($admin)->get(route('admin.catalog.icons.index'));
        $response->assertOk();
        $response->assertSee('Icons');

        // 2. Create
        $response = $this->actingAs($admin)->get(route('admin.catalog.icons.create'));
        $response->assertOk();

        // 3. Store
        $iconName = 'Delivery Test ' . uniqid();
        $response = $this->actingAs($admin)->post(route('admin.catalog.icons.store'), [
            'library_id' => $library->id,
            'name' => $iconName,
            'icon_type' => 'fontawesome',
            'icon_value' => 'fa-solid fa-truck-ramp-box',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.catalog.icons.index'));

        $icon = Icon::where('name', $iconName)->firstOrFail();
        $this->assertSame('fa-solid fa-truck-ramp-box', $icon->icon_value);

        // 4. Edit
        $response = $this->actingAs($admin)->get(route('admin.catalog.icons.edit', $icon));
        $response->assertOk();

        // 5. Update
        $response = $this->actingAs($admin)->put(route('admin.catalog.icons.update', $icon), [
            'library_id' => $library->id,
            'name' => $iconName . ' Modified',
            'icon_type' => 'fontawesome',
            'icon_value' => 'fa-solid fa-box-open',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.catalog.icons.index'));
        $this->assertSame('fa-solid fa-box-open', $icon->fresh()->icon_value);

        // 6. API Search
        $response = $this->actingAs($admin)->get(route('admin.catalog.icons.api.search', ['q' => 'Modified']));
        $response->assertOk();
        $response->assertJsonFragment(['name' => $iconName . ' Modified']);

        // 7. Toggle Active
        $this->actingAs($admin)->post(route('admin.catalog.icons.toggle-active', $icon));
        $this->assertFalse($icon->fresh()->is_active);

        // 8. Destroy
        $this->actingAs($admin)->delete(route('admin.catalog.icons.destroy', $icon));
        $this->assertNull(Icon::find($icon->id));
    }

    public function test_supplier_profile_manager_services_with_icon_and_order(): void
    {
        $supplier = $this->makeSupplierUser();

        $library = IconLibrary::firstOrCreate(
            ['slug' => 'fontawesome'],
            ['name' => 'FontAwesome 6', 'type' => 'Local', 'is_active' => true]
        );

        $icon = Icon::firstOrCreate(
            ['name' => 'Speed Delivery Test'],
            [
                'library_id' => $library->id,
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-bolt',
                'is_active' => true,
            ]
        );

        $this->actingAs($supplier)->post(route('supplier.company.profile.services.store'), [
            'title' => 'Express On-Site Support',
            'description' => 'Rapid response technical assistance.',
            'status' => 'active',
            'icon_id' => $icon->id,
            'sort_order' => 1,
        ])->assertSessionDoesntHaveErrors();

        $savedService = Service::where('title', 'Express On-Site Support')->firstOrFail();
        $this->assertEquals($icon->id, $savedService->icon_id);
        $this->assertEquals(1, $savedService->sort_order);
        $this->assertSame('active', $savedService->status);
        $this->assertEquals($supplier->account->id, $savedService->serviceable_id);

        // Test update
        $this->actingAs($supplier)->put(route('supplier.company.profile.services.update', $savedService), [
            'title' => 'Express On-Site Support Updated',
            'status' => 'active',
            'icon_id' => $icon->id,
            'sort_order' => 5,
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame('Express On-Site Support Updated', $savedService->fresh()->title);
        $this->assertEquals(5, $savedService->fresh()->sort_order);
    }
}
