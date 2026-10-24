<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The hero banner is now cropped server-side (Intervention) from a stored
 * original, so the supplier can re-crop later without re-uploading:
 *   banner          → the cropped image every page displays (unchanged)
 *   banner_original → the uncropped source (scaled to ≤3000px), re-crop input
 *   banner_crop     → {x, y, width, height} of the last crop, in original pixels
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->string('banner_original')->nullable()->after('banner');
            $table->json('banner_crop')->nullable()->after('banner_original');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->dropColumn(['banner_original', 'banner_crop']);
        });
    }
};
