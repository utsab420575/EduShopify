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
        // Mirrors quotation_item_offers exactly — a revision must freeze
        // every offer (not just the primary), since it represents the
        // complete quotation state at that point in time, not a summary.
        Schema::create('quotation_revision_item_offers', function (Blueprint $table) {
            $table->id();
            // Explicit short constraint name: the default auto-generated one
            // ("quotation_revision_item_offers_quotation_revision_item_id_foreign")
            // exceeds MySQL's 64-char identifier limit.
            $table->foreignId('quotation_revision_item_id')
                ->constrained('quotation_revision_items', indexName: 'qrio_revision_item_id_fk')
                ->cascadeOnDelete();
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
            $table->unsignedInteger('delivery_time')->nullable();
            $table->enum('status', ['draft', 'submitted', 'withdrawn'])->default('draft');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            // The live quotation_item_offers row this was snapshotted from —
            // nullOnDelete (not cascade): the live offer can be edited away
            // or removed by a later revision without touching frozen history.
            $table->foreignId('source_offer_id')->nullable()->constrained('quotation_item_offers')->nullOnDelete();
            $table->timestamps();

            $table->index(['quotation_revision_item_id', 'is_primary'], 'qrio_revision_item_primary_idx');
            $table->index(['quotation_revision_item_id', 'is_selected'], 'qrio_revision_item_selected_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_revision_item_offers');
    }
};
