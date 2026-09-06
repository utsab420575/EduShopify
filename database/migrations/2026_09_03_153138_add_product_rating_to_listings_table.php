<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Mirrors supplier_profiles.rating / reviews_count exactly — a
            // maintained aggregate, recalculated only by
            // ReviewModerationService, never computed live.
            $table->decimal('product_rating', 3, 2)->default(0);
            $table->unsignedInteger('product_reviews_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['product_rating', 'product_reviews_count']);
        });
    }
};
