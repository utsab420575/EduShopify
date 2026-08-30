<?php

namespace Database\Seeders;

use App\Models\VisibilityType;
use Illuminate\Database\Seeder;

class VisibilityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'          => 'Direct RFQ',
                'code'          => 'direct',
                'engine_type'   => 'invited',
                'max_suppliers' => 1,
                'description'   => 'Send directly to 1 specific supplier for single-source negotiation.',
                'sort_order'    => 1,
                'is_active'     => true,
            ],
            [
                'name'          => 'Invited RFQ',
                'code'          => 'invited',
                'engine_type'   => 'invited',
                'max_suppliers' => null,
                'description'   => 'Send to a curated shortlist of selected suppliers.',
                'sort_order'    => 2,
                'is_active'     => true,
            ],
            [
                'name'          => 'Open RFQ',
                'code'          => 'open_matching',
                'engine_type'   => 'open',
                'max_suppliers' => null,
                'description'   => 'Make available on the marketplace to eligible matching suppliers.',
                'sort_order'    => 3,
                'is_active'     => true,
            ],
            [
                'name'          => 'All Suppliers',
                'code'          => 'broadcast_all',
                'engine_type'   => 'open',
                'max_suppliers' => null,
                'description'   => 'Broadcast across the marketplace to all registered verified suppliers.',
                'sort_order'    => 4,
                'is_active'     => true,
            ],
        ];

        foreach ($types as $type) {
            VisibilityType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
