<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icon_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // CDN, Local, Custom
            $table->text('cdn_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('icons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_id')->constrained('icon_libraries')->cascadeOnDelete();
            $table->string('name');
            $table->string('icon_type'); // fontawesome, svg, image_url
            $table->text('icon_value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('icon_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icons');
        Schema::dropIfExists('icon_libraries');
    }
};
