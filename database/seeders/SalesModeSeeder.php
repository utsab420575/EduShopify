<?php

namespace Database\Seeders;

use App\Models\SalesMode;
use Illuminate\Database\Seeder;

class SalesModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            ['name' => 'RFQ Only',        'code' => 'rfq_only',       'description' => 'Buyers can only submit RFQs; no direct purchase.',       'is_active' => true, 'sort_order' => 1],
            ['name' => 'Direct Purchase', 'code' => 'direct_purchase','description' => 'Buyers can purchase directly from the catalog.',         'is_active' => true, 'sort_order' => 2],
            ['name' => 'Both',            'code' => 'both',            'description' => 'Both direct purchase and RFQ submission are available.', 'is_active' => true, 'sort_order' => 3],
        ];

        foreach ($modes as $mode) {
            SalesMode::firstOrCreate(['code' => $mode['code']], $mode);
        }
    }
}
