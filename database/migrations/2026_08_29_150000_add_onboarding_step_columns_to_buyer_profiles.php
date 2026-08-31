<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors 2026_08_29_130000_add_onboarding_step_columns_to_supplier_profiles
     * — same resume-tracking mechanism, now for the Buyer wizard.
     */
    public function up(): void
    {
        Schema::table('buyer_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('buyer_profiles', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('logo');
            }
            if (! Schema::hasColumn('buyer_profiles', 'current_step')) {
                $table->unsignedTinyInteger('current_step')->nullable()->after('profile_completed_at');
            }
            if (! Schema::hasColumn('buyer_profiles', 'max_step_reached')) {
                $table->unsignedTinyInteger('max_step_reached')->nullable()->after('current_step');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buyer_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('buyer_profiles', 'max_step_reached')) {
                $table->dropColumn('max_step_reached');
            }
            if (Schema::hasColumn('buyer_profiles', 'current_step')) {
                $table->dropColumn('current_step');
            }
            if (Schema::hasColumn('buyer_profiles', 'profile_photo')) {
                $table->dropColumn('profile_photo');
            }
        });
    }
};
