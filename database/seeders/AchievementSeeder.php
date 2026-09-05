<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

/**
 * Master achievement catalogue (admin-managed). Accounts don't own rows
 * here — they claim these via AccountAchievement, reviewed per-account.
 */
class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            ['name' => 'Founding Supplier', 'slug' => 'founding-supplier', 'type' => 'milestone', 'description' => 'One of the first suppliers to join the Edushopify marketplace.'],
            ['name' => 'Top Rated', 'slug' => 'top-rated', 'type' => 'performance', 'description' => 'Maintains an outstanding buyer satisfaction rating.'],
            ['name' => 'Fast Responder', 'slug' => 'fast-responder', 'type' => 'performance', 'description' => 'Consistently responds to quotation requests within hours.'],
            ['name' => 'ISE Exhibitor', 'slug' => 'ise-exhibitor', 'type' => 'exhibition', 'description' => 'Exhibited at the Integrated Systems Europe trade show.'],
            ['name' => 'BETT Exhibitor', 'slug' => 'bett-exhibitor', 'type' => 'exhibition', 'description' => 'Exhibited at the BETT education technology show.'],
            ['name' => 'Global Reach', 'slug' => 'global-reach', 'type' => 'milestone', 'description' => 'Ships to institutions across 50+ countries.'],
            ['name' => 'Quality Assured', 'slug' => 'quality-assured', 'type' => 'compliance', 'description' => 'Holds recognized quality-management certification.'],
            ['name' => 'ISO 9001 Partner', 'slug' => 'iso-9001-partner', 'type' => 'compliance', 'description' => 'Partnered with an ISO 9001 certified manufacturer.'],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                [
                    'name' => $achievement['name'],
                    'description' => $achievement['description'],
                    'type' => $achievement['type'],
                    'is_active' => true,
                ]
            );
        }
    }
}
