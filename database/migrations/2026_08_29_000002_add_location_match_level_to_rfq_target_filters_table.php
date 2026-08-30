<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the buyer control how strictly open_matching narrows suppliers by
 * location: none (skip location filtering entirely — delivery location and
 * supplier-matching location are deliberately independent), or country/
 * state/city (broader supplier service areas satisfy a narrower request).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_target_filters', function (Blueprint $table) {
            $table->enum('location_match_level', ['none', 'country', 'state', 'city'])
                ->default('none')
                ->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_target_filters', function (Blueprint $table) {
            $table->dropColumn('location_match_level');
        });
    }
};
