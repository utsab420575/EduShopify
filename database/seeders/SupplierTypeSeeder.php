<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['en' => 'Manufacturer',       'ar' => 'مصنّع',                  'uz' => 'Ishlab chiqaruvchi'],
            ['en' => 'Distributor',        'ar' => 'موزّع',                  'uz' => 'Distribyutor'],
            ['en' => 'Wholesaler',         'ar' => 'تاجر جملة',              'uz' => 'Ulgurji sotuvchi'],
            ['en' => 'Reseller',           'ar' => 'بائع تجزئة',             'uz' => 'Qayta sotuvchi'],
            ['en' => 'Importer',           'ar' => 'مستورد',                 'uz' => 'Importyor'],
            ['en' => 'Exporter',           'ar' => 'مصدّر',                  'uz' => 'Eksportyor'],
            ['en' => 'System Integrator',  'ar' => 'مكامل أنظمة',            'uz' => 'Tizim integrator'],
            ['en' => 'Service Provider',   'ar' => 'مزوّد خدمات',            'uz' => 'Xizmat ko\'rsatuvchi'],
            ['en' => 'Consultant',         'ar' => 'مستشار',                 'uz' => 'Maslahatchi'],
            ['en' => 'Publisher',          'ar' => 'ناشر',                   'uz' => 'Nashriyotchi'],
            ['en' => 'Training Provider',  'ar' => 'مزوّد تدريب',            'uz' => 'O\'quv xizmati provayderi'],
            ['en' => 'OEM',                'ar' => 'مصنّع أصلي للمعدات',     'uz' => 'OEM ishlab chiqaruvchi'],
        ];

        foreach ($types as $i => $name) {
            $slug = Str::slug($name['en']);
            DB::table('supplier_types')->updateOrInsert(
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
