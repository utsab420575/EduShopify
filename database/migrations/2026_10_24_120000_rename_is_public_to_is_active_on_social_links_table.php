<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * social_links.is_public → is_active. Same meaning (only active links are
 * shown on the public supplier profile), existing values carried over as-is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->renameColumn('is_public', 'is_active');
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->renameColumn('is_active', 'is_public');
        });
    }
};
