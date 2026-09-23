<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scopes quotation_item_attribute_values to the individual offer it belongs
 * to, not just the Product Response as a whole. Before this, only whichever
 * offer happened to be primary ever had its structured attribute values
 * persisted here — alternatives' answers only ever reached the free-text
 * quotation_item_offers.specifications JSON. Every offer (primary or
 * alternative) now gets its own attribute-value rows.
 *
 * quotation_item_id is kept (not dropped) for direct item-level queries —
 * it's set alongside quotation_item_offer_id on every row, always
 * consistent with that offer's own parent item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_item_attribute_values', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_item_offer_id')->nullable()->after('quotation_item_id');
            $table->foreign('quotation_item_offer_id', 'qiav_offer_fk')
                ->references('id')->on('quotation_item_offers')->cascadeOnDelete();
            $table->index(['quotation_item_offer_id', 'attribute_id'], 'qiav_offer_attr_idx');
        });

        // Backfill: attach any existing rows to their item's primary offer
        // (the only offer they could have come from under the old logic),
        // so historical data isn't orphaned under the new scoping. A
        // correlated subquery (not UPDATE...JOIN) so this runs on both MySQL
        // and SQLite (the test suite's driver) without a driver check.
        DB::statement(
            'update quotation_item_attribute_values '
            .'set quotation_item_offer_id = ('
            .'    select id from quotation_item_offers '
            .'    where quotation_item_offers.quotation_item_id = quotation_item_attribute_values.quotation_item_id '
            .'    and quotation_item_offers.is_primary = 1 limit 1'
            .') '
            .'where quotation_item_offer_id is null'
        );
    }

    public function down(): void
    {
        Schema::table('quotation_item_attribute_values', function (Blueprint $table) {
            $table->dropForeign('qiav_offer_fk');
            $table->dropIndex('qiav_offer_attr_idx');
            $table->dropColumn('quotation_item_offer_id');
        });
    }
};
