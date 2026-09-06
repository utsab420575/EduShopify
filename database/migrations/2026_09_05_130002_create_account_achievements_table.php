<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An account's claim on a master achievement — admin-reviewed. Only
 * status = approved rows are shown on the account profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('achievement_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('proof')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'achievement_id']);
            $table->index('status');

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('achievement_id')->references('id')->on('achievements')->cascadeOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_achievements');
    }
};
