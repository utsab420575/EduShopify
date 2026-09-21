<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Mirrors rfqs.current_step / rfqs.max_completed_step — current_step
            // is whichever step tab is active (can move backward), while
            // max_completed_step only ever ratchets up and is what gates step
            // navigation and drives the completion bar.
            $table->unsignedTinyInteger('current_step')->default(1)->after('current_revision_no');
            $table->unsignedTinyInteger('max_completed_step')->default(1)->after('current_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['current_step', 'max_completed_step']);
        });
    }
};
