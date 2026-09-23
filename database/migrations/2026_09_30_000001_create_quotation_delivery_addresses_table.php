<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every delivery address for a quotation — including what plays the role of
 * "primary" (sort_order 0, always rendered as "Address 1") — lives here.
 * Unlike rfqs (which denormalizes its primary address onto the rfqs row
 * itself and keeps rfq_delivery_addresses for additional ones only), there's
 * no split here: simpler, one source of truth, nothing to keep in sync
 * between two places.
 *
 * Seeded once, on first save of a new quotation, by copying the RFQ's own
 * primary address plus its rfq_delivery_addresses rows (see
 * QuotationService::copyDeliveryAddressesFromRfq()) — after that the
 * supplier can freely edit/replace them without ever touching the RFQ's own
 * addresses again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_delivery_addresses');
    }
};
