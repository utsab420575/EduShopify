<?php

namespace Database\Seeders;

use App\Models\PricingType;
use Illuminate\Database\Seeder;

class PricingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Fixed Catalog Price',  'code' => 'fixed',       'description' => 'Direct purchase with a transparent base price.', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Quote Only / Custom',  'code' => 'quote_only',  'description' => 'Buyers request a custom quotation. No public price shown.', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Fixed Price + RFQ',    'code' => 'rfq_enabled', 'description' => 'Both direct purchase and custom RFQ supported.', 'is_active' => true, 'sort_order' => 3],
        ];

        foreach ($types as $type) {
            PricingType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
