<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create listing_variant_media Table ─────────────────────────────
        if (!Schema::hasTable('listing_variant_media')) {
            Schema::create('listing_variant_media', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('listing_variant_id');
                $table->unsignedBigInteger('media_id');
                $table->boolean('is_primary')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['listing_variant_id', 'media_id'], 'listing_variant_media_unique');
                $table->index(['listing_variant_id', 'sort_order'], 'listing_variant_media_sort_index');

                $table->foreign('listing_variant_id', 'listing_variant_media_variant_fk')
                    ->references('id')
                    ->on('listing_variants')
                    ->cascadeOnDelete();

                $table->foreign('media_id', 'listing_variant_media_media_fk')
                    ->references('id')
                    ->on('media')
                    ->cascadeOnDelete();
            });
        }

        // ── 2. Update listing_variants Table ──────────────────────────────────
        Schema::table('listing_variants', function (Blueprint $table) {
            if (Schema::hasColumn('listing_variants', 'image_media_ids')) {
                $table->dropColumn('image_media_ids');
            }

            if (!Schema::hasColumn('listing_variants', 'combination_key')) {
                $table->string('combination_key', 64)->nullable()->after('name');
                $table->unique(['listing_id', 'combination_key'], 'listing_variants_listing_comb_unique');
            }
        });

        // ── 3. Clean up orphaned foreign references before adding FKs ─────────
        DB::statement("UPDATE listings SET main_category_id = NULL WHERE main_category_id IS NOT NULL AND main_category_id NOT IN (SELECT id FROM categories)");
        DB::statement("UPDATE listings SET brand_id = NULL WHERE brand_id IS NOT NULL AND brand_id NOT IN (SELECT id FROM brands)");
        DB::statement("UPDATE listings SET unit_id = NULL WHERE unit_id IS NOT NULL AND unit_id NOT IN (SELECT id FROM units)");
        DB::statement("UPDATE listings SET approved_by_user_id = NULL WHERE approved_by_user_id IS NOT NULL AND approved_by_user_id NOT IN (SELECT id FROM users)");
        DB::statement("UPDATE listings SET primary_image_media_id = NULL WHERE primary_image_media_id IS NOT NULL AND primary_image_media_id NOT IN (SELECT id FROM media)");

        DB::statement("DELETE FROM listing_variants WHERE listing_id NOT IN (SELECT id FROM listings)");
        DB::statement("UPDATE listing_variants SET unit_id = NULL WHERE unit_id IS NOT NULL AND unit_id NOT IN (SELECT id FROM units)");

        DB::statement("DELETE FROM listing_attribute_values WHERE listing_id NOT IN (SELECT id FROM listings)");
        DB::statement("DELETE FROM listing_attribute_values WHERE attribute_id NOT IN (SELECT id FROM attributes)");
        DB::statement("UPDATE listing_attribute_values SET attribute_value_id = NULL WHERE attribute_value_id IS NOT NULL AND attribute_value_id NOT IN (SELECT id FROM attribute_values)");

        DB::statement("DELETE FROM listing_tier_prices WHERE listing_id NOT IN (SELECT id FROM listings)");
        DB::statement("DELETE FROM listing_tier_prices WHERE listing_variant_id IS NOT NULL AND listing_variant_id NOT IN (SELECT id FROM listing_variants)");

        DB::statement("DELETE FROM listing_variant_attributes WHERE listing_variant_id NOT IN (SELECT id FROM listing_variants)");
        DB::statement("DELETE FROM listing_variant_attributes WHERE attribute_id NOT IN (SELECT id FROM attributes)");
        DB::statement("UPDATE listing_variant_attributes SET attribute_value_id = NULL WHERE attribute_value_id IS NOT NULL AND attribute_value_id NOT IN (SELECT id FROM attribute_values)");

        // ── 4. Add Missing Foreign Key Constraints on listings ─────────────────
        Schema::table('listings', function (Blueprint $table) {
            $table->foreign('supplier_account_id', 'listings_supplier_account_id_fk')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();

            $table->foreign('created_by_user_id', 'listings_created_by_user_id_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('main_category_id', 'listings_main_category_id_fk')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();

            $table->foreign('brand_id', 'listings_brand_id_fk')
                ->references('id')
                ->on('brands')
                ->nullOnDelete();

            $table->foreign('unit_id', 'listings_unit_id_fk')
                ->references('id')
                ->on('units')
                ->nullOnDelete();

            $table->foreign('approved_by_user_id', 'listings_approved_by_user_id_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('primary_image_media_id', 'listings_primary_image_media_id_fk')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
        });

        // ── 5. Add Missing Foreign Key Constraints on listing_variants ─────────
        Schema::table('listing_variants', function (Blueprint $table) {
            $table->foreign('listing_id', 'listing_variants_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();

            $table->foreign('unit_id', 'listing_variants_unit_id_fk')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
        });

        // ── 6. Add Foreign Key Constraints on listing_attribute_values ─────────
        Schema::table('listing_attribute_values', function (Blueprint $table) {
            $table->foreign('listing_id', 'listing_attribute_values_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();

            $table->foreign('attribute_id', 'listing_attribute_values_attribute_id_fk')
                ->references('id')
                ->on('attributes')
                ->cascadeOnDelete();

            $table->foreign('attribute_value_id', 'listing_attribute_values_attribute_value_id_fk')
                ->references('id')
                ->on('attribute_values')
                ->nullOnDelete();
        });

        // ── 7. Add Foreign Key Constraints on listing_tier_prices ─────────────
        Schema::table('listing_tier_prices', function (Blueprint $table) {
            $table->foreign('listing_id', 'listing_tier_prices_listing_id_fk')
                ->references('id')
                ->on('listings')
                ->cascadeOnDelete();

            $table->foreign('listing_variant_id', 'listing_tier_prices_listing_variant_id_fk')
                ->references('id')
                ->on('listing_variants')
                ->cascadeOnDelete();
        });

        // ── 8. Add Foreign Key Constraints on listing_variant_attributes ───────
        Schema::table('listing_variant_attributes', function (Blueprint $table) {
            $table->foreign('listing_variant_id', 'listing_variant_attributes_variant_id_fk')
                ->references('id')
                ->on('listing_variants')
                ->cascadeOnDelete();

            $table->foreign('attribute_id', 'listing_variant_attributes_attribute_id_fk')
                ->references('id')
                ->on('attributes')
                ->cascadeOnDelete();

            $table->foreign('attribute_value_id', 'listing_variant_attributes_value_id_fk')
                ->references('id')
                ->on('attribute_values')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_variant_media');

        Schema::table('listing_variants', function (Blueprint $table) {
            if (Schema::hasColumn('listing_variants', 'combination_key')) {
                $table->dropUnique('listing_variants_listing_comb_unique');
                $table->dropColumn('combination_key');
            }
        });
    }
};
