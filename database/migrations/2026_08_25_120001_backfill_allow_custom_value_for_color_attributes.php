<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Color attributes previously let suppliers type a freeform custom
     * color/hex unconditionally. Now that the custom-value input is gated
     * behind attributes.allow_custom_value, flip it on for every existing
     * color attribute so that capability isn't silently removed.
     */
    public function up(): void
    {
        DB::table('attributes')
            ->where('input_type', 'color')
            ->update(['allow_custom_value' => true]);
    }

    public function down(): void
    {
        // Intentionally not reversed: we can't distinguish attributes an
        // admin explicitly opted into afterward from ones this backfill
        // touched, so reverting would risk turning off a deliberate choice.
    }
};
