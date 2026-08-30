<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_attribute_values', function (Blueprint $table) {
            $table->string('custom_value')->nullable()->after('attribute_value_id');
        });

        // Reconcile existing data: color attributes previously stored a supplier's
        // freeform "custom color" text in value_text (with no attribute_value_id),
        // since that was the only custom-value input that existed before this
        // feature. Move that text into the new, dedicated custom_value column so
        // value_text goes back to meaning only "a genuine text/textarea answer".
        DB::table('listing_attribute_values')
            ->join('attributes', 'attributes.id', '=', 'listing_attribute_values.attribute_id')
            ->where('attributes.input_type', 'color')
            ->whereNull('listing_attribute_values.attribute_value_id')
            ->whereNotNull('listing_attribute_values.value_text')
            ->update([
                'listing_attribute_values.custom_value' => DB::raw('listing_attribute_values.value_text'),
                'listing_attribute_values.value_text'   => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('listing_attribute_values', function (Blueprint $table) {
            $table->dropColumn('custom_value');
        });
    }
};
