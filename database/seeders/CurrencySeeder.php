<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('currencies')->insert([
            [
                'code'           => 'USD',
                'name'           => 'US Dollar',
                'symbol'         => '$',
                'exchange_rate'  => 1.000000,
                'is_default'     => true,
                'is_active'      => true,
                'decimal_places' => 2,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'code'           => 'AED',
                'name'           => 'UAE Dirham',
                'symbol'         => 'د.إ',
                'exchange_rate'  => 3.672500,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'code'           => 'EUR',
                'name'           => 'Euro',
                'symbol'         => '€',
                'exchange_rate'  => 0.920000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'code'           => 'GBP',
                'name'           => 'British Pound',
                'symbol'         => '£',
                'exchange_rate'  => 0.790000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'code'           => 'UZS',
                'name'           => 'Uzbek Som',
                'symbol'         => 'soʻm',
                'exchange_rate'  => 12700.000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
