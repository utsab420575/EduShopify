<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Polymorphic on purpose: today the only socialable is Account (buyer
     * and supplier are both just Account rows, distinguished by capability,
     * so a plain account_id would already cover both) — but this leaves
     * room for a genuinely different model (e.g. a public Listing/Brand
     * page) to carry its own social links later without a schema change.
     */
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('socialable_type');
            $table->unsignedBigInteger('socialable_id');
            $table->foreignId('social_platform_id')->constrained('social_platforms')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('handle', 150)->nullable();
            $table->string('label', 150)->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['socialable_type', 'socialable_id'], 'social_links_socialable_index');
            // One row per platform per socialable — editing a link updates
            // it in place rather than accumulating duplicate LinkedIn rows.
            $table->unique(['socialable_type', 'socialable_id', 'social_platform_id'], 'social_links_unique_per_platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
