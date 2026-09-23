<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the quotation-level "Overall Lead Time (Days)" field in the
 * Detail & Delivery step's UI with an actual calendar date — a genuinely
 * different concept from lead_time_days (an integer day-count), which stays
 * in the schema unchanged since it's still used at the item level and by the
 * "clone from previous quotation" matching logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->date('expected_delivery_date')->nullable()->after('valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('expected_delivery_date');
        });
    }
};
