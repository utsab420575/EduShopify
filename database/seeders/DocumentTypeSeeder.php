<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'             => 'Trade License',
                'slug'             => 'trade-license',
                'description'      => 'Valid trade license issued by the relevant authority',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 10,
            ],
            [
                'name'             => 'Company Registration Certificate',
                'slug'             => 'company-registration-certificate',
                'description'      => 'Official company registration document',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 20,
            ],
            [
                'name'             => 'Tax Certificate',
                'slug'             => 'tax-certificate',
                'description'      => 'Tax registration or clearance certificate',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 30,
            ],
            [
                'name'             => 'VAT Certificate',
                'slug'             => 'vat-certificate',
                'description'      => 'VAT registration certificate if applicable',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 40,
            ],
            [
                'name'             => 'ISO Certificate',
                'slug'             => 'iso-certificate',
                'description'      => 'ISO 9001 or other quality/standards certifications',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 50,
            ],
            [
                'name'             => 'Authorization Letter',
                'slug'             => 'authorization-letter',
                'description'      => 'Authorization letter from brand/manufacturer if acting as reseller',
                'accepted_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'      => 5120,
                'sort_order'       => 60,
            ],
        ];

        foreach ($types as $type) {
            DB::table('document_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'name'             => $type['name'],
                    'description'      => $type['description'],
                    'accepted_formats' => json_encode($type['accepted_formats']),
                    'max_size_kb'      => $type['max_size_kb'],
                    'is_active'        => true,
                    'sort_order'       => $type['sort_order'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }
    }
}
