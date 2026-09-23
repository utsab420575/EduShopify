<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds 'unsubmitted' — logged by QuotationService::undoSubmit() when a
 * supplier pulls a submitted quotation back to draft. MySQL needs a raw
 * MODIFY COLUMN (Blueprint::change() can't express an enum value list
 * change portably); SQLite has no such restriction — Schema::table()
 * ->change() rebuilds the table natively (no doctrine/dbal needed on
 * Laravel 11+), so it can use the normal Blueprint API directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotation_activities MODIFY COLUMN activity_type ENUM('drafted', 'submitted', 'unsubmitted', 'viewed_by_buyer', 'buyer_messaged', 'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted', 'rejected', 'expired') NOT NULL");

            return;
        }

        Schema::table('quotation_activities', function (Blueprint $table) {
            $table->enum('activity_type', [
                'drafted', 'submitted', 'unsubmitted', 'viewed_by_buyer', 'buyer_messaged',
                'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted',
                'rejected', 'expired',
            ])->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotation_activities MODIFY COLUMN activity_type ENUM('drafted', 'submitted', 'viewed_by_buyer', 'buyer_messaged', 'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted', 'rejected', 'expired') NOT NULL");

            return;
        }

        Schema::table('quotation_activities', function (Blueprint $table) {
            $table->enum('activity_type', [
                'drafted', 'submitted', 'viewed_by_buyer', 'buyer_messaged',
                'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted',
                'rejected', 'expired',
            ])->change();
        });
    }
};
