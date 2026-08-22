<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V4.3 settings table (Layer 1 — Spatie Laravel Settings storage)
     * group_name + name are the composite key used by Spatie.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group_name', 120);
            $table->string('name', 160);
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();

            $table->unique(['group_name', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

