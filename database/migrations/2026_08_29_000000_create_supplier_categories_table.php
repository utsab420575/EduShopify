<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * supplier_categories — "what a supplier is capable of supplying," distinct
 * from listings ("what they currently publish"). Drives open_matching RFQ
 * supplier eligibility; a supplier may be capable of responding to a custom
 * requirement even without a currently published listing in that category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['supplier_account_id', 'category_id']);
            $table->index('category_id');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_categories');
    }
};
