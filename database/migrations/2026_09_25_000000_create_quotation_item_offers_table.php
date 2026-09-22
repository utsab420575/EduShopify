<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_item_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_item_id')->constrained('quotation_items')->cascadeOnDelete();
            $table->enum('offer_method', ['marketplace', 'custom', 'document', 'copy_spec'])->default('marketplace');
            $table->foreignId('marketplace_product_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->foreignId('offered_variant_id')->nullable()->constrained('listing_variants')->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('quantity', 15, 3)->default(1);
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('custom_unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 7, 4)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->unsignedInteger('delivery_time')->nullable(); // lead time in days
            $table->enum('status', ['draft', 'submitted', 'withdrawn'])->default('draft');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_selected')->default(false); // Buyer selects winning offer
            $table->unsignedInteger('sort_order')->default(0); // Supplier display order
            $table->timestamps();

            $table->index(['quotation_item_id', 'is_primary']);
            $table->index(['quotation_item_id', 'is_selected']);
            $table->index(['quotation_item_id', 'sort_order']);
            $table->index(['quotation_item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_item_offers');
    }
};
