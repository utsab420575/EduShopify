<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add wizard progress, cover media, and scoped unique SKU to listings table
        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'primary_image_media_id')) {
                $table->unsignedBigInteger('primary_image_media_id')->nullable()->after('brand_id');
                $table->index('primary_image_media_id');
            }

            if (! Schema::hasColumn('listings', 'setup_step')) {
                $table->unsignedTinyInteger('setup_step')->default(1)->after('approval_status');
            }

            if (! Schema::hasColumn('listings', 'setup_completed_at')) {
                $table->timestamp('setup_completed_at')->nullable()->after('setup_step');
            }

            if (! Schema::hasColumn('listings', 'last_autosaved_at')) {
                $table->timestamp('last_autosaved_at')->nullable()->after('setup_completed_at');
            }
        });

        // Add scoped unique SKU index if it doesn't already exist
        try {
            Schema::table('listings', function (Blueprint $table) {
                $table->unique(['supplier_account_id', 'sku'], 'listings_supplier_sku_unique');
            });
        } catch (\Throwable $e) {
            // Index might already exist
        }

        // 2. Add custom_value to listing_attribute_values if missing
        if (! Schema::hasColumn('listing_attribute_values', 'custom_value')) {
            Schema::table('listing_attribute_values', function (Blueprint $table) {
                $table->string('custom_value', 255)->nullable()->after('value_json');
            });
        }

        // 3. Create attribute_custom_value_reviews table for admin review and reconciliation
        if (! Schema::hasTable('attribute_custom_value_reviews')) {
            Schema::create('attribute_custom_value_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('custom_value', 255);
                $table->unsignedBigInteger('supplier_account_id')->nullable();
                $table->unsignedBigInteger('first_listing_id')->nullable();
                $table->unsignedBigInteger('submitted_by_user_id')->nullable();
                $table->unsignedInteger('usage_count')->default(1);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('resulting_attribute_value_id')->nullable();
                $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
                $table->text('review_comment')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->unique(['attribute_id', 'custom_value'], 'attr_custom_val_unique');
                $table->index(['status', 'attribute_id']);

                $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
                $table->foreign('supplier_account_id')->references('id')->on('accounts')->nullOnDelete();
                $table->foreign('first_listing_id')->references('id')->on('listings')->nullOnDelete();
                $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('resulting_attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
                $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_custom_value_reviews');

        Schema::table('listings', function (Blueprint $table) {
            try {
                $table->dropUnique('listings_supplier_sku_unique');
            } catch (\Throwable $e) {}

            try {
                $table->dropIndex(['primary_image_media_id']);
            } catch (\Throwable $e) {}

            $columnsToDrop = array_intersect(
                ['primary_image_media_id', 'setup_step', 'setup_completed_at', 'last_autosaved_at'],
                Schema::getColumnListing('listings')
            );
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
