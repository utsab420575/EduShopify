<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon')->nullable();
            $table->string('base_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // This is a small, fixed reference list the app requires to function
        // (like a status or type lookup), not optional sample data — seeded
        // directly here so every environment has it as soon as it migrates.
        $now = now();
        DB::table('social_platforms')->insert([
            ['name' => 'Facebook', 'slug' => 'facebook', 'base_url' => 'https://facebook.com/', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LinkedIn', 'slug' => 'linkedin', 'base_url' => 'https://linkedin.com/', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Instagram', 'slug' => 'instagram', 'base_url' => 'https://instagram.com/', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'YouTube', 'slug' => 'youtube', 'base_url' => 'https://youtube.com/', 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'X', 'slug' => 'x', 'base_url' => 'https://x.com/', 'sort_order' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TikTok', 'slug' => 'tiktok', 'base_url' => 'https://tiktok.com/', 'sort_order' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('social_platforms');
    }
};
