<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a catch-all "Other" entry to the fixed social_platforms reference
 * list so a buyer/supplier can attach a social link that isn't one of the
 * named platforms — its custom name is stored in social_links.label.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('social_platforms')->where('slug', 'other')->exists()) {
            return;
        }

        $now = now();
        DB::table('social_platforms')->insert([
            'name' => 'Other',
            'slug' => 'other',
            'base_url' => null,
            'sort_order' => 999,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('social_platforms')->where('slug', 'other')->delete();
    }
};
