<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code'           => 'USD',
                'name'           => 'US Dollar',
                'symbol'         => '$',
                'exchange_rate'  => 1.00000000,
                'is_default'     => true,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'BDT',
                'name'           => 'Bangladeshi Taka',
                'symbol'         => '৳',
                'exchange_rate'  => 120.00000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'EUR',
                'name'           => 'Euro',
                'symbol'         => '€',
                'exchange_rate'  => 0.92000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'GBP',
                'name'           => 'British Pound',
                'symbol'         => '£',
                'exchange_rate'  => 0.79000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'INR',
                'name'           => 'Indian Rupee',
                'symbol'         => '₹',
                'exchange_rate'  => 85.50000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'AED',
                'name'           => 'UAE Dirham',
                'symbol'         => 'د.إ',
                'exchange_rate'  => 3.67250000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'SAR',
                'name'           => 'Saudi Riyal',
                'symbol'         => '﷼',
                'exchange_rate'  => 3.75000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'CAD',
                'name'           => 'Canadian Dollar',
                'symbol'         => 'CA$',
                'exchange_rate'  => 1.38000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'AUD',
                'name'           => 'Australian Dollar',
                'symbol'         => 'A$',
                'exchange_rate'  => 1.55000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'SGD',
                'name'           => 'Singapore Dollar',
                'symbol'         => 'S$',
                'exchange_rate'  => 1.34000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'JPY',
                'name'           => 'Japanese Yen',
                'symbol'         => '¥',
                'exchange_rate'  => 155.00000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 0,
            ],
            [
                'code'           => 'CNY',
                'name'           => 'Chinese Yuan',
                'symbol'         => '¥',
                'exchange_rate'  => 7.25000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 2,
            ],
            [
                'code'           => 'UZS',
                'name'           => 'Uzbek Som',
                'symbol'         => 'soʻm',
                'exchange_rate'  => 12700.00000000,
                'is_default'     => false,
                'is_active'      => true,
                'decimal_places' => 0,
            ],
        ];

        foreach ($currencies as $curr) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $curr['code']],
                array_merge($curr, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
