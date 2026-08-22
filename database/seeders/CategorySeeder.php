<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Seed Units
        $units = [
            ['name' => 'Piece', 'symbol' => 'pc', 'unit_type' => 'count'],
            ['name' => 'Box', 'symbol' => 'box', 'unit_type' => 'pack'],
            ['name' => 'Set', 'symbol' => 'set', 'unit_type' => 'pack'],
            ['name' => 'Carton', 'symbol' => 'ctn', 'unit_type' => 'pack'],
            ['name' => 'Hour', 'symbol' => 'hr', 'unit_type' => 'time'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                [
                    'symbol'          => $unit['symbol'],
                    'unit_type'       => $unit['unit_type'],
                    'scope'           => 'global',
                    'approval_status' => 'approved',
                    'is_active'       => true,
                ]
            );
        }

        // Seed Brands
        $brands = [
            ['name' => 'Dell Education', 'slug' => 'dell-education'],
            ['name' => 'HP School Solutions', 'slug' => 'hp-school-solutions'],
            ['name' => 'SmartBoard Systems', 'slug' => 'smartboard-systems'],
            ['name' => 'Fisher Scientific Ed', 'slug' => 'fisher-scientific-ed'],
            ['name' => 'Herman Miller Learning', 'slug' => 'herman-miller-learning'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name'            => $brand['name'],
                    'owner_type'      => 'global',
                    'approval_status' => 'approved',
                    'is_active'       => true,
                ]
            );
        }

        // Seed Categories
        $categories = [
            [
                'name'        => 'EdTech & Hardware',
                'slug'        => 'edtech-hardware',
                'type'        => 'product',
                'description' => 'Laptops, tablets, interactive displays, and classroom hardware.',
            ],
            [
                'name'        => 'Science & Lab Equipment',
                'slug'        => 'science-lab-equipment',
                'type'        => 'product',
                'description' => 'Microscopes, chemicals, lab glassware, and STEM kits.',
            ],
            [
                'name'        => 'Classroom Furniture',
                'slug'        => 'classroom-furniture',
                'type'        => 'product',
                'description' => 'Desks, ergonomic chairs, whiteboards, and storage cabinets.',
            ],
            [
                'name'        => 'Digital Curriculum & Software',
                'slug'        => 'digital-curriculum-software',
                'type'        => 'product',
                'description' => 'E-learning platforms, LMS subscriptions, and digital textbooks.',
            ],
            [
                'name'        => 'IT & Maintenance Services',
                'slug'        => 'it-maintenance-services',
                'type'        => 'service',
                'description' => 'Network installation, hardware repair, and IT support services.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'            => $cat['name'],
                    'type'            => $cat['type'],
                    'description'     => $cat['description'],
                    'approval_status' => 'approved',
                    'is_active'       => true,
                ]
            );
        }
    }
}
