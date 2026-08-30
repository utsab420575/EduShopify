<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Drop primary_image_media_id from listing_variants ─────────────
        Schema::table('listing_variants', function (Blueprint $table) {
            if (Schema::hasColumn('listing_variants', 'primary_image_media_id')) {
                $table->dropColumn('primary_image_media_id');
            }
        });

        // ── 2. Add primary_variant_guard to listing_variant_media ─────────────
        // Ensures at the database engine level that each variant has AT MOST ONE is_primary = 1
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('listing_variant_media') && !Schema::hasColumn('listing_variant_media', 'primary_variant_guard')) {
            DB::statement("
                ALTER TABLE `listing_variant_media`
                ADD COLUMN `primary_variant_guard` BIGINT UNSIGNED
                GENERATED ALWAYS AS (IF(`is_primary` = 1, `listing_variant_id`, NULL)) VIRTUAL,
                ADD UNIQUE KEY `listing_variant_one_primary_unique` (`primary_variant_guard`)
            ");
        }

        // ── 3. Clean duplicates & orphans on details & logs before adding FKs ──
        // Ensure product_details unique listing_id
        DB::statement("DELETE FROM product_details WHERE listing_id NOT IN (SELECT id FROM listings)");
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                DELETE p1 FROM product_details p1
                INNER JOIN product_details p2 
                WHERE p1.id > p2.id AND p1.listing_id = p2.listing_id
            ");
        }

        // Ensure service_details unique listing_id
        DB::statement("DELETE FROM service_details WHERE listing_id NOT IN (SELECT id FROM listings)");
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                DELETE s1 FROM service_details s1
                INNER JOIN service_details s2 
                WHERE s1.id > s2.id AND s1.listing_id = s2.listing_id
            ");
        }

        // Ensure listing_categories clean
        DB::statement("DELETE FROM listing_categories WHERE listing_id NOT IN (SELECT id FROM listings)");
        DB::statement("DELETE FROM listing_categories WHERE category_id NOT IN (SELECT id FROM categories)");
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                DELETE c1 FROM listing_categories c1
                INNER JOIN listing_categories c2 
                WHERE c1.id > c2.id AND c1.listing_id = c2.listing_id AND c1.category_id = c2.category_id
            ");
        }

        // Ensure listing_change_logs clean
        DB::statement("DELETE FROM listing_change_logs WHERE listing_id NOT IN (SELECT id FROM listings)");
        DB::statement("DELETE FROM listing_change_logs WHERE changed_by_user_id IS NOT NULL AND changed_by_user_id NOT IN (SELECT id FROM users)");

        // ── 4. Add constraints to product_details ─────────────────────────────
        Schema::table('product_details', function (Blueprint $table) {
            // Check unique listing_id
            $indexes = Schema::getIndexes('product_details');
            $hasUnique = collect($indexes)->contains(fn ($idx) => $idx['name'] === 'product_details_listing_id_unique' || ($idx['unique'] && in_array('listing_id', $idx['columns'])));
            if (!$hasUnique) {
                $table->unique('listing_id', 'product_details_listing_id_unique');
            }

            $table->foreign('listing_id', 'product_details_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();
        });

        // ── 5. Add constraints to service_details ─────────────────────────────
        Schema::table('service_details', function (Blueprint $table) {
            $indexes = Schema::getIndexes('service_details');
            $hasUnique = collect($indexes)->contains(fn ($idx) => $idx['name'] === 'service_details_listing_id_unique' || ($idx['unique'] && in_array('listing_id', $idx['columns'])));
            if (!$hasUnique) {
                $table->unique('listing_id', 'service_details_listing_id_unique');
            }

            $table->foreign('listing_id', 'service_details_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();
        });

        // ── 6. Add constraints to listing_categories ──────────────────────────
        Schema::table('listing_categories', function (Blueprint $table) {
            $table->foreign('listing_id', 'listing_categories_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();

            $table->foreign('category_id', 'listing_categories_category_id_fk')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });

        // ── 7. Add constraints to listing_change_logs ─────────────────────────
        Schema::table('listing_change_logs', function (Blueprint $table) {
            $table->foreign('listing_id', 'listing_change_logs_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();

            $table->foreign('changed_by_user_id', 'listing_change_logs_user_id_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // down logic
    }
};
