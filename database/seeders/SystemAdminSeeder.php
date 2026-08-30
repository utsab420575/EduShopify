<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds to create ONLY the System Account and Super Admin.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        // 1. Create or retrieve the System Organization Account
        $systemAccount = Account::firstOrCreate(
            ['account_number' => 'SYSTEM'],
            [
                'account_type'      => 'organization',
                'is_system_account' => true,
                'display_name'      => 'Edushopify Platform',
                'slug'              => 'edushopify-platform',
                'status'            => 'active',
                'approved_at'       => now(),
            ]
        );

        // Set Spatie team context to the system account (account_id)
        $permissionRegistrar->setPermissionsTeamId($systemAccount->id);

        // 2. Ensure Super Admin & Admin Roles exist
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            [
                'display_name'     => 'Super Admin',
                'capability_scope' => 'platform',
                'description'      => 'Full platform root administrator with unrestricted access.',
                'is_system'        => true,
                'is_owner_role'    => true,
                'is_active'        => true,
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            [
                'display_name'     => 'Admin',
                'capability_scope' => 'platform',
                'description'      => 'Platform administrative staff.',
                'is_system'        => true,
                'is_owner_role'    => false,
                'is_active'        => true,
            ]
        );

        // 3. Create or update the Super Admin user
        $adminEmail = 'admin@edushopify.com';
        $adminUser = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => 'Super Admin',
                'phone'             => '+10000000000',
                'password'          => Hash::make('11111111'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
                'locale'            => 'en',
            ]
        );

        // 4. Link Super Admin to System Account as Primary Owner
        AccountMember::updateOrCreate(
            [
                'account_id' => $systemAccount->id,
                'user_id'    => $adminUser->id,
            ],
            [
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        // 5. Update System Account Primary Owner link
        $systemAccount->update([
            'primary_owner_user_id' => $adminUser->id,
            'approved_by_user_id'   => $adminUser->id,
        ]);

        // 6. Assign Super Admin and Admin roles under system account_id
        $adminUser->activateTeamContext();
        $adminUser->syncRoles([$superAdminRole, $adminRole]);

        // 7. Clear Spatie permission cache
        $permissionRegistrar->forgetCachedPermissions();

        $this->command?->info("System Account (ID: {$systemAccount->id}) and Super Admin ({$adminEmail}) created successfully!");
    }
}
