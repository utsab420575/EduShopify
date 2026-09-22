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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Which specific offer the buyer selected (or the supplier's
            // primary, if the buyer never explicitly picked one) — the PO
            // line's price/spec/delivery-time come from this offer, not
            // just the parent Product Response's denormalized fields.
            $table->foreignId('quotation_item_offer_id')->nullable()
                ->after('quotation_item_id')
                ->constrained('quotation_item_offers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_item_offer_id');
        });
    }
};
