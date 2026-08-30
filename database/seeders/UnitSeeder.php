<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'symbol' => 'pc', 'unit_type' => 'count'],
            ['name' => 'Item', 'symbol' => 'item', 'unit_type' => 'count'],
            ['name' => 'Box', 'symbol' => 'bx', 'unit_type' => 'count'],
            ['name' => 'Set', 'symbol' => 'set', 'unit_type' => 'count'],
            ['name' => 'Pack', 'symbol' => 'pk', 'unit_type' => 'count'],
            ['name' => 'Carton', 'symbol' => 'ctn', 'unit_type' => 'count'],
            ['name' => 'Dozen', 'symbol' => 'dz', 'unit_type' => 'count'],
            ['name' => 'Pair', 'symbol' => 'pr', 'unit_type' => 'count'],
            ['name' => 'Bundle', 'symbol' => 'bdl', 'unit_type' => 'count'],
            ['name' => 'Roll', 'symbol' => 'roll', 'unit_type' => 'count'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'unit_type' => 'weight'],
            ['name' => 'Gram', 'symbol' => 'g', 'unit_type' => 'weight'],
            ['name' => 'Pound', 'symbol' => 'lb', 'unit_type' => 'weight'],
            ['name' => 'Meter', 'symbol' => 'm', 'unit_type' => 'length'],
            ['name' => 'Centimeter', 'symbol' => 'cm', 'unit_type' => 'length'],
            ['name' => 'Foot', 'symbol' => 'ft', 'unit_type' => 'length'],
            ['name' => 'Inch', 'symbol' => 'in', 'unit_type' => 'length'],
            ['name' => 'Liter', 'symbol' => 'L', 'unit_type' => 'volume'],
            ['name' => 'Milliliter', 'symbol' => 'ml', 'unit_type' => 'volume'],
            ['name' => 'Square Meter', 'symbol' => 'sqm', 'unit_type' => 'area'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['name' => $unit['name']],
                [
                    'symbol'          => $unit['symbol'],
                    'unit_type'       => $unit['unit_type'],
                    'scope'           => 'global',
                    'approval_status' => 'approved',
                    'is_active'       => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }
    }
}
