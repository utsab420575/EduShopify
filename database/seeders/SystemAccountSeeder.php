<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class SystemAccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::firstOrCreate(
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
    }
}
