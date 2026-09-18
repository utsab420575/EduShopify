<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            // Forward-only "furthest step reached" — unlike current_step
            // (which just tracks whichever tab is currently active and can
            // move backward), this only ever ratchets up, so it's what
            // gates step-tab navigation and drives the completion bar.
            $table->unsignedTinyInteger('max_completed_step')->default(1)->after('current_step');
        });

        // Best-effort backfill for drafts that already progressed past step 1
        // before this column existed — current_step is whatever step they
        // last viewed, so it's a reasonable floor even if imperfect for a
        // draft they navigated back to step 1 on.
        DB::table('rfqs')->where('current_step', '>', 1)->update([
            'max_completed_step' => DB::raw('current_step'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn('max_completed_step');
        });
    }
};
