<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * quotation_item_id was left in place (denormalized) when
 * quotation_item_offer_id was added, purely for direct item-level queries.
 * Nothing actually needs that anymore — every consumer already resolves
 * through a specific offer (QuotationItemOffer::attributeValues()) — so it's
 * dropped here rather than kept as a second foreign key that has to stay in
 * sync with the offer's own parent item forever.
 */
return new class extends Migration
{
    public function up(): void
    {
        // On SQLite (the test suite's driver), the FK is defined inline in
        // the table and DROP COLUMN fails outright unless it's dropped
        // first — so dropForeign() must always run there. On MySQL, whether
        // the original migration's FK constraint actually exists varies by
        // environment (it's absent on this app's current dev database
        // despite being declared back in the original migration), so that
        // one driver checks information_schema directly rather than
        // assuming, to avoid dropForeign() failing the whole statement.
        $driver = Schema::getConnection()->getDriverName();
        $hasForeignKey = $driver !== 'mysql' || DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'quotation_item_attribute_values')
            ->where('CONSTRAINT_NAME', 'quotation_item_attribute_values_quotation_item_id_foreign')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        Schema::table('quotation_item_attribute_values', function (Blueprint $table) use ($hasForeignKey) {
            if ($hasForeignKey) {
                $table->dropForeign(['quotation_item_id']);
            }
            $table->dropIndex('qiav_item_attr_idx');
            $table->dropColumn('quotation_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_item_attribute_values', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_item_id')->nullable()->after('id');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->cascadeOnDelete();
            $table->index(['quotation_item_id', 'attribute_id'], 'qiav_item_attr_idx');
        });
    }
};
