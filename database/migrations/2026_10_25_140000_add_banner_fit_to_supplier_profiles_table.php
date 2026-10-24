<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How the cropped banner fills the public hero box (full width × 280px):
 *   cover   → fills the box, overflow is cut off (previous behaviour)
 *   contain → whole image visible, blurred copy fills the empty bars
 *   fill    → stretched to exactly the box (may distort)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->string('banner_fit', 10)->default('cover')->after('banner_crop');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->dropColumn('banner_fit');
        });
    }
};
