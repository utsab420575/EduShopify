<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuyerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['en' => 'Schools',              'ar' => 'المدارس',                  'uz' => 'Maktablar'],
            ['en' => 'Universities',         'ar' => 'الجامعات',                 'uz' => 'Universitetlar'],
            ['en' => 'Colleges',             'ar' => 'الكليات',                  'uz' => 'Kollejlar'],
            ['en' => 'Government Organizations', 'ar' => 'الجهات الحكومية',      'uz' => 'Davlat tashkilotlari'],
            ['en' => 'NGOs',                 'ar' => 'المنظمات غير الحكومية',    'uz' => 'NNT\'lar'],
            ['en' => 'Training Centers',     'ar' => 'مراكز التدريب',            'uz' => 'O\'quv markazlari'],
            ['en' => 'Consultants',          'ar' => 'الاستشاريون',              'uz' => 'Maslahatchilar'],
            ['en' => 'System Integrators',   'ar' => 'مكاملو الأنظمة',           'uz' => 'Tizim integratorlari'],
            ['en' => 'Resellers',            'ar' => 'الموزعون',                 'uz' => 'Qayta sotuvchilar'],
            ['en' => 'Distributors',         'ar' => 'الموردون',                 'uz' => 'Distribyutorlar'],
        ];

        foreach ($types as $i => $name) {
            $slug = Str::slug($name['en']);
            DB::table('buyer_types')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name'       => json_encode(['en' => $name['en']]),
                    'is_active'  => true,
                    'sort_order' => ($i + 1) * 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
