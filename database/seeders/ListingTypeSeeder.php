<?php

namespace Database\Seeders;

use App\Models\ListingType;
use Illuminate\Database\Seeder;

class ListingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Product', 'code' => 'product', 'description' => 'Physical or digital products listed for sale or quotation.', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Service', 'code' => 'service', 'description' => 'Professional services offered by the supplier.',             'is_active' => true, 'sort_order' => 2],
        ];

        foreach ($types as $type) {
            ListingType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
