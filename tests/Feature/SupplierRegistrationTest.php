<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_registration_creates_user_account_membership_capability_and_profile_skeleton(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);

        $service = app(AccountRegistrationService::class);

        $user = $service->register([
            'account_type' => 'organization',
            'capability'   => 'supplier',
            'name'         => 'Jane Supplier',
            'email'        => 'supplier@example.com',
            'phone'        => '+971500001111',
            'password'     => 'Password123!',
            'organization_display_name' => 'EduTech Solutions LLC',
        ]);

        $account = $user->account;

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('pending_verification', $user->status);
        $this->assertEquals('draft', $account->status);

        // Verify primary owner membership
        $this->assertDatabaseHas('account_members', [
            'account_id'       => $account->id,
            'user_id'          => $user->id,
            'member_type'      => 'owner',
            'is_primary_owner' => true,
        ]);

        // Verify supplier capability created in draft state. Capability is
        // resolved through capability_type_id -> capability_types.code, not
        // the removed account_capabilities.capability column.
        $supplierCapabilityTypeId = \App\Models\CapabilityType::where('code', 'supplier')->value('id');
        $this->assertDatabaseHas('account_capabilities', [
            'account_id'         => $account->id,
            'capability_type_id' => $supplierCapabilityTypeId,
            'status'             => 'draft',
        ]);

        // Verify supplier profile skeleton created
        $this->assertDatabaseHas('supplier_profiles', [
            'account_id'    => $account->id,
            'display_name'  => 'EduTech Solutions LLC',
            'contact_email' => 'supplier@example.com',
            'profile_completed_at' => null,
        ]);
    }
}
