<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // units — must precede attributes which references units.id
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['global', 'supplier_custom'])->default('global');
            $table->unsignedBigInteger('supplier_account_id')->nullable();
            $table->string('name', 100);
            $table->string('symbol', 30)->nullable();
            $table->string('unit_type', 80)->nullable();
            $table->enum('approval_status', ['approved', 'pending', 'rejected'])->default('approved');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['scope', 'supplier_account_id', 'name']);
            $table->index('supplier_account_id');
            $table->index('approval_status');
            $table->index('is_active');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // categories (self-referencing)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->enum('type', ['product', 'service', 'both'])->default('both');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by_account_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('type');
            $table->index('approval_status');
            $table->index(['is_active', 'sort_order']);

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('created_by_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('category_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('suggested_by_user_id');
            $table->unsignedBigInteger('parent_category_id')->nullable();
            $table->string('proposed_name');
            $table->enum('proposed_type', ['product', 'service', 'both'])->default('both');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->unsignedBigInteger('resulting_category_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_account_id', 'status']);
            $table->index('parent_category_id');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('suggested_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('parent_category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('resulting_category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->enum('owner_type', ['global', 'supplier'])->default('global');
            $table->unsignedBigInteger('supplier_account_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_type');
            $table->index('supplier_account_id');
            $table->index('approval_status');
            $table->index(['is_active', 'sort_order']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('input_type', ['text', 'textarea', 'number', 'select', 'multi_select', 'boolean', 'date', 'color']);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('placeholder')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_variant')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('input_type');
            $table->index('is_filterable');
            $table->index('is_variant');
            $table->index(['is_active', 'sort_order']);

            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value');
            $table->string('slug');
            $table->string('color_hex', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['attribute_id', 'slug']);
            $table->index(['attribute_id', 'is_active', 'sort_order']);

            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
        });

        Schema::create('category_attribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('attribute_id');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_variant')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'attribute_id']);
            $table->index(['category_id', 'sort_order']);
            $table->index('attribute_id');

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
        });

        Schema::create('attribute_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('suggested_by_user_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('proposed_name');
            $table->string('proposed_input_type', 30);
            $table->string('proposed_unit_name', 100)->nullable();
            $table->json('proposed_values')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->unsignedBigInteger('resulting_attribute_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_account_id', 'status']);
            $table->index('category_id');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('suggested_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('resulting_attribute_id')->references('id')->on('attributes')->nullOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_suggestions');
        Schema::dropIfExists('category_attribute');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('category_suggestions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('units');
    }
};
