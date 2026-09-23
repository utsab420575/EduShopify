<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Superseded before it ever shipped: this originally widened the
 * activity_type ENUM to add 'awarded'/'award_cancelled'/'award_declined'.
 * 2026_09_30_000005_add_actor_role_and_user_id_to_quotation_activities_table
 * (written concurrently, same day) converted the column from enum to a
 * plain unconstrained VARCHAR(50) instead — a better fix for the same
 * underlying problem (new activity types no longer need a schema
 * migration at all). Running this migration's original ENUM-based up()
 * AFTER that conversion silently re-narrowed the column back to an enum
 * and broke inserts of any value it didn't happen to list (e.g.
 * 'shortlisted', 'rejected_by_supplier') — see
 * 2026_10_01_000001_ensure_quotation_activities_activity_type_is_unconstrained.php,
 * which restores VARCHAR(50) and is now the actual fix. This migration is
 * kept only because it already has a row in the migrations table; both
 * methods are intentionally no-ops.
 */
return new class extends Migration
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
};
