<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public badge presentation for an achievement, set by Platform Admin:
 *   display_badge → short text on the public badge (falls back to name)
 *   badge_color   → admin-picked colour, #rrggbb (NULL = automatic colour
 *                   by achievement type); see App\Support\BadgeColor
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->string('display_badge', 60)->nullable()->after('badge_icon');
            $table->string('badge_color', 20)->nullable()->after('display_badge');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['display_badge', 'badge_color']);
        });
    }
};
