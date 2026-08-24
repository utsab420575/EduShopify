<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountLocation;
use App\Models\AccountMember;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('11111111');
        $permissionRegistrar = app(PermissionRegistrar::class);

        // 1. Ensure System Account exists
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

        // 2. Platform Staff User Definitions
        $staffMembers = [
            [
                'name'              => 'Super Admin',
                'email'             => 'admin@edushopify.com',
                'phone'             => '+10000000000',
                'member_type'       => 'owner',
                'is_primary_owner'  => true,
                'roles'             => ['super_admin', 'admin'],
            ],
            [
                'name'              => 'Admin Staff',
                'email'             => 'staff@edushopify.com',
                'phone'             => '+10000000001',
                'member_type'       => 'member',
                'is_primary_owner'  => false,
                'roles'             => ['admin_staff'],
            ],
            [
                'name'              => 'Content Moderator',
                'email'             => 'moderator@edushopify.com',
                'phone'             => '+10000000002',
                'member_type'       => 'member',
                'is_primary_owner'  => false,
                'roles'             => ['moderator'],
            ],
            [
                'name'              => 'Support Agent',
                'email'             => 'support@edushopify.com',
                'phone'             => '+10000000003',
                'member_type'       => 'member',
                'is_primary_owner'  => false,
                'roles'             => ['support_agent'],
            ],
            [
                'name'              => 'Finance Staff',
                'email'             => 'finance@edushopify.com',
                'phone'             => '+10000000004',
                'member_type'       => 'member',
                'is_primary_owner'  => false,
                'roles'             => ['finance_staff'],
            ],
        ];

        $primaryOwnerUser = null;

        // Set Spatie team context to the system account
        $permissionRegistrar->setPermissionsTeamId($systemAccount->id);

        foreach ($staffMembers as $staff) {
            $user = User::firstOrCreate(
                ['email' => $staff['email']],
                [
                    'name'              => $staff['name'],
                    'phone'             => $staff['phone'],
                    'password'          => $passwordHash,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'status'            => 'active',
                    'locale'            => 'en',
                ]
            );

            // Create or update membership in the SYSTEM account
            AccountMember::firstOrCreate(
                [
                    'account_id' => $systemAccount->id,
                    'user_id'    => $user->id,
                ],
                [
                    'member_type'      => $staff['member_type'],
                    'is_primary_owner' => $staff['is_primary_owner'],
                    'status'           => 'active',
                    'joined_at'        => now(),
                ]
            );

            // Assign scoped Spatie roles
            foreach ($staff['roles'] as $roleName) {
                if (! $user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                }
            }

            if ($staff['is_primary_owner']) {
                $primaryOwnerUser = $user;
            }
        }

        // 3. Link primary owner back to system account if not already linked
        if ($primaryOwnerUser && (! $systemAccount->primary_owner_user_id || ! $systemAccount->approved_by_user_id)) {
            $systemAccount->update([
                'primary_owner_user_id' => $primaryOwnerUser->id,
                'approved_by_user_id'   => $primaryOwnerUser->id,
            ]);
        }

        // 4. Seed system account primary location if country exists
        $country = Country::where('iso2', 'US')->first() ?? Country::first();
        if ($country && $primaryOwnerUser) {
            $state = State::where('country_id', $country->id)->first();
            $city = $state ? City::where('state_id', $state->id)->first() : null;

            AccountLocation::firstOrCreate(
                [
                    'account_id'    => $systemAccount->id,
                    'location_type' => 'primary',
                ],
                [
                    'label'              => 'Edushopify Headquarters',
                    'contact_name'       => 'Edushopify Support Team',
                    'phone'              => '+10000000000',
                    'country_id'         => $country->id,
                    'state_id'           => $state?->id,
                    'city_id'            => $city?->id,
                    'address_line_1'     => '100 Innovation Way, Tech Hub',
                    'postal_code'        => '10001',
                    'is_primary'         => true,
                    'is_active'          => true,
                    'created_by_user_id' => $primaryOwnerUser->id,
                ]
            );
        }

        // 5. Forget cached permissions
        $permissionRegistrar->forgetCachedPermissions();
    }
}

