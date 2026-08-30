<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds is_optional_addon to both the live and snapshot quotation-item
 * tables (an add-on line has rfq_item_id = NULL and must never be treated
 * as a missing/extra RFQ response — spec optional add-on section), and
 * decline_reason to rfq_supplier_queue for the decline/ignore flow (spec
 * §33) — the queue's status enum already contains 'ignored', it was just
 * never paired with a reason column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->boolean('is_optional_addon')->default(false)->after('is_alternative');
        });

        Schema::table('quotation_revision_items', function (Blueprint $table) {
            $table->boolean('is_optional_addon')->default(false)->after('is_alternative');
        });

        Schema::table('rfq_supplier_queue', function (Blueprint $table) {
            $table->text('decline_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('is_optional_addon');
        });

        Schema::table('quotation_revision_items', function (Blueprint $table) {
            $table->dropColumn('is_optional_addon');
        });

        Schema::table('rfq_supplier_queue', function (Blueprint $table) {
            $table->dropColumn('decline_reason');
        });
    }
};
