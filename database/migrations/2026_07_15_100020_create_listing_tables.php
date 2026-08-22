<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->enum('listing_type', ['product', 'service']);
            $table->string('listing_number', 50)->unique();
            $table->unsignedBigInteger('main_category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('sku', 100)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->enum('pricing_type', ['fixed', 'quote_only', 'rfq_enabled'])->default('quote_only');
            $table->enum('sales_mode', ['rfq_only', 'direct_purchase', 'both'])->default('rfq_only');
            $table->decimal('base_price', 15, 2)->nullable();
            $table->decimal('compare_at_price', 15, 2)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->decimal('min_order_quantity', 15, 3)->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->json('extra_specs')->nullable();
            $table->enum('approval_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['supplier_account_id', 'slug']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('main_category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->unique();
            $table->enum('product_type', ['simple', 'variable'])->default('simple');
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'limited', 'on_request'])->default('on_request');
            $table->decimal('stock_quantity', 15, 3)->nullable();
            $table->decimal('weight', 15, 3)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('support_terms')->nullable();
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
        });

        Schema::create('service_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->unique();
            $table->enum('service_mode', ['onsite', 'remote', 'hybrid'])->nullable();
            $table->decimal('duration_value', 12, 2)->nullable();
            $table->enum('duration_unit', ['hour', 'day', 'week', 'month', 'session'])->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->text('service_terms')->nullable();
            $table->text('support_terms')->nullable();
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
        });

        Schema::create('listing_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['listing_id', 'category_id']);

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::create('listing_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
        });

        Schema::create('listing_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->string('sku', 100)->nullable();
            $table->string('name')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('compare_at_price', 15, 2)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'limited', 'on_request'])->default('on_request');
            $table->decimal('stock_quantity', 15, 3)->nullable();
            $table->decimal('weight', 15, 3)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->decimal('min_order_quantity', 15, 3)->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->unsignedBigInteger('primary_image_media_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('listing_variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_variant_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('custom_value')->nullable();
            $table->timestamps();

            $table->unique(['listing_variant_id', 'attribute_id'], 'listing_var_attr_var_attr_unique');
            $table->index('attribute_id');
            $table->index('attribute_value_id');

            $table->foreign('listing_variant_id')->references('id')->on('listing_variants')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
        });

        Schema::create('listing_tier_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('listing_variant_id')->nullable();
            $table->decimal('min_quantity', 15, 3);
            $table->decimal('max_quantity', 15, 3)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->string('currency_code', 10);
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->foreign('listing_variant_id')->references('id')->on('listing_variants')->cascadeOnDelete();
        });

        Schema::create('listing_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->json('changed_fields');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_change_logs');
        Schema::dropIfExists('listing_tier_prices');
        Schema::dropIfExists('listing_variant_attributes');
        Schema::dropIfExists('listing_variants');
        Schema::dropIfExists('listing_attribute_values');
        Schema::dropIfExists('listing_categories');
        Schema::dropIfExists('service_details');
        Schema::dropIfExists('product_details');
        Schema::dropIfExists('listings');
    }
};
