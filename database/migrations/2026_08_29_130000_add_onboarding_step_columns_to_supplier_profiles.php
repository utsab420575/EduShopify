<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks resume position for the Supplier Application wizard so a user
     * who logs out mid-application lands back where they left off instead of
     * restarting at step 1. current_step is exactly where they were sitting;
     * max_step_reached is the furthest step ever unlocked (drives which
     * steps the step-bar allows jumping back to).
     */
    public function up(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('supplier_profiles', 'current_step')) {
                $table->unsignedTinyInteger('current_step')->nullable()->after('profile_completed_at');
            }
            if (! Schema::hasColumn('supplier_profiles', 'max_step_reached')) {
                $table->unsignedTinyInteger('max_step_reached')->nullable()->after('current_step');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_profiles', 'max_step_reached')) {
                $table->dropColumn('max_step_reached');
            }
            if (Schema::hasColumn('supplier_profiles', 'current_step')) {
                $table->dropColumn('current_step');
            }
        });
    }
};
