<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_conversion_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->enum('from_type', ['individual']);
            $table->enum('to_type', ['organization']);
            $table->string('proposed_display_name', 200);
            $table->json('proposed_organization_data');
            $table->json('submitted_documents')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'revision_required', 'cancelled'])
                  ->default('draft');
            $table->unsignedBigInteger('submitted_by_user_id');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('account_type_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('conversion_request_id');
            $table->enum('old_type', ['individual']);
            $table->enum('new_type', ['organization']);
            $table->string('old_display_name', 200);
            $table->string('new_display_name', 200);
            $table->json('old_snapshot');
            $table->json('new_snapshot');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->unsignedBigInteger('approved_by_user_id');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('conversion_request_id')->references('id')->on('account_conversion_requests')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_type_changes');
        Schema::dropIfExists('account_conversion_requests');
    }
};
