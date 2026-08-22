<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_capabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->enum('capability', ['buyer', 'supplier']);
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'revision_required', 'suspended'])
                  ->default('draft');
            $table->unsignedSmallInteger('application_attempts')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->text('revision_reason')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->unsignedBigInteger('applied_by_user_id')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'capability']);

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('applied_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('capability_application_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_capability_id');
            $table->unsignedSmallInteger('attempt_no');
            $table->json('submitted_snapshot');
            $table->enum('status', ['submitted', 'approved', 'rejected', 'revision_required', 'withdrawn']);
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['account_capability_id', 'attempt_no'], 'cap_app_hist_cap_id_attempt_unique');

            $table->foreign('account_capability_id')->references('id')->on('account_capabilities')->cascadeOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capability_application_history');
        Schema::dropIfExists('account_capabilities');
    }
};
