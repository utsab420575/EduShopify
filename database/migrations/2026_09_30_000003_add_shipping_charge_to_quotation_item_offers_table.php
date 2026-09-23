<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moves shipping cost from one flat quotations.shipping_charge value to
 * per-offer, entered alongside Unit Price/Tax Rate/Discount in Step 1.
 * quotations.shipping_charge is NOT dropped — it becomes a computed
 * aggregate (sum of each item's primary offer's shipping_charge, written on
 * every save by QuotationService) instead of directly editable, so
 * everything already reading it (revision snapshots, the buyer comparison
 * view, purchase-order generation) keeps working unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_item_offers', function (Blueprint $table) {
            $table->decimal('shipping_charge', 15, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_item_offers', function (Blueprint $table) {
            $table->dropColumn('shipping_charge');
        });
    }
};
