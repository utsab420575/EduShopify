<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026_09_30_000005_add_actor_role_and_user_id_to_quotation_activities_table
 * converts activity_type from enum to an unconstrained VARCHAR(50) so new
 * activity types never need another enum-widening migration again — but its
 * `ALTER TABLE ... MODIFY COLUMN` is MySQL-only syntax, silently caught and
 * skipped on SQLite. That left the SQLite test DB stuck on whatever enum
 * values existed at the time (missing 'shortlisted', 'awarded',
 * 'award_cancelled', 'award_declined', 'rejected_by_supplier'), so
 * QuotationActivityService::record() throws a CHECK-constraint violation
 * for any of them under the test suite. Finishes that conversion for
 * SQLite using Blueprint::change() (no doctrine/dbal needed on Laravel 11+,
 * confirmed to correctly rebuild the table under the hood). A no-op on
 * MySQL, which is already a plain varchar(50) by this point.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('quotation_activities', function (Blueprint $table) {
            $table->string('activity_type', 50)->change();
        });
    }

    public function down(): void
    {
        // Nothing to revert to — the prior state was an enum missing
        // values later code already depends on; reverting would just
        // reintroduce the bug this migration fixes.
    }
};
