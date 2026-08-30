<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('listing_variants', 'image_media_ids')) {
                $table->json('image_media_ids')->nullable()->after('primary_image_media_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listing_variants', function (Blueprint $table) {
            if (Schema::hasColumn('listing_variants', 'image_media_ids')) {
                $table->dropColumn('image_media_ids');
            }
        });
    }
};
