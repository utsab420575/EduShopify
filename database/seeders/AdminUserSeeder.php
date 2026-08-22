<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@edushopify.com'],
            [
                'name'              => 'Super Admin',
                'phone'             => '+10000000000',
                'password'          => Hash::make('11111111'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        if ($systemAccount) {
            AccountMember::firstOrCreate(
                ['user_id' => $admin->id],
                [
                    'account_id'       => $systemAccount->id,
                    'member_type'      => 'owner',
                    'is_primary_owner' => true,
                    'status'           => 'active',
                    'joined_at'        => now(),
                ]
            );

            app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
            $admin->assignRole('admin');
        }
    }
}
