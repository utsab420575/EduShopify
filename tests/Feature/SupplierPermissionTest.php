<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use App\Policies\ListingPolicy;
use App\Services\AccountRegistrationService;
use Database\Seeders\SupplierPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_supplier_can_create_draft_listing_but_cannot_publish(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SupplierPermissionSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization',
            'capability'   => 'supplier',
            'name'         => 'Pending Perm Supplier',
            'email'        => 'pendingperm@example.com',
            'phone'        => '+971500005555',
            'password'     => 'Password123!',
            'organization_display_name' => 'Pending Perm Corp',
        ]);

        $account = $user->account;

        // Mark email verified & activate user/account
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);

        $user->activateTeamContext();
        \Spatie\Permission\PermissionRegistrar::class;
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->assignRole('primary_owner');

        $policy = new ListingPolicy();

        // Pending capability status
        $cap = $account->supplierCapability;
        $cap->update(['status' => 'pending']);

        // 1. Pending supplier CAN create draft listing (Section 53 & Section 86)
        $this->assertTrue($policy->create($user));

        // 2. Pending supplier CANNOT publish listing (Section 86)
        $listing = Listing::create([
            'supplier_account_id' => $account->id,
            'created_by_user_id'  => $user->id,
            'listing_number' => 'LST-10001',
            'name'         => 'Test Item',
            'slug'         => 'test-item-' . uniqid(),
            'listing_type' => 'product',
            'status'       => 'draft',
            'price'        => 100,
        ]);

        $this->assertFalse($policy->publish($user, $listing));
    }
}
