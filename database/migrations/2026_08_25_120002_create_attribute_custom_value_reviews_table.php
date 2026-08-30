<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin's decision history for supplier-submitted custom ("Other")
     * attribute values. Deliberately decoupled from listing_attribute_values:
     * a review row only gets created the first time an admin actually acts
     * on a given (attribute, custom value) pair (approve or ignore) — the
     * review worklist itself is built by aggregating listing_attribute_values
     * directly and left-joining this table for status, not by pre-seeding a
     * row per submission.
     */
    public function up(): void
    {
        Schema::create('attribute_custom_value_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('custom_value');
            $table->enum('status', ['pending', 'approved', 'ignored'])->default('pending');
            $table->unsignedBigInteger('resulting_attribute_value_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['attribute_id', 'custom_value']);
            $table->index('status');

            $table->foreign('attribute_id', 'acvr_attribute_id_fk')
                ->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('resulting_attribute_value_id', 'acvr_resulting_attribute_value_id_fk')
                ->references('id')->on('attribute_values')->nullOnDelete();
            $table->foreign('reviewed_by_user_id', 'acvr_reviewed_by_user_id_fk')
                ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_custom_value_reviews');
    }
};
