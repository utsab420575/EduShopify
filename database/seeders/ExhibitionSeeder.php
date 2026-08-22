<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = [
            ['name' => 'BETT Show', 'slug' => 'bett-show', 'website' => 'https://www.bettshow.com'],
            ['name' => 'GESS Dubai', 'slug' => 'gess-dubai', 'website' => 'https://www.gessdubai.com'],
            ['name' => 'Integrated Systems Europe', 'slug' => 'ise-europe', 'website' => 'https://www.iseurope.org'],
            ['name' => 'GITEX Global', 'slug' => 'gitex-global', 'website' => 'https://www.gitex.com'],
        ];

        foreach ($exhibitions as $i => $ex) {
            DB::table('exhibitions')->insert([
                'name'       => $ex['name'],
                'slug'       => $ex['slug'],
                'website'    => $ex['website'],
                'logo'       => null,
                'is_active'  => true,
                'sort_order' => ($i + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
