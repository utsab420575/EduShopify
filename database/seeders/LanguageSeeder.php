<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('languages')->insert([
            [
                'code'        => 'en',
                'name'        => 'English',
                'native_name' => 'English',
                'direction'   => 'ltr',
                'flag'        => '🇬🇧',
                'is_active'   => true,
                'is_default'  => true,
                'sort_order'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'ar',
                'name'        => 'Arabic',
                'native_name' => 'العربية',
                'direction'   => 'rtl',
                'flag'        => '🇸🇦',
                'is_active'   => true,
                'is_default'  => false,
                'sort_order'  => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'uz',
                'name'        => 'Uzbek',
                'native_name' => "O'zbek",
                'direction'   => 'ltr',
                'flag'        => '🇺🇿',
                'is_active'   => true,
                'is_default'  => false,
                'sort_order'  => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
