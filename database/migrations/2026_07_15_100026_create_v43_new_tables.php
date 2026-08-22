<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // V4.3 — account_dashboard_preferences (#98)
        Schema::create('account_dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->enum('default_mode', ['buyer', 'supplier']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });

        // V4.3 — role_requests (#99)
        Schema::create('role_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->string('role_name');
            $table->string('display_name', 180);
            $table->string('capability_scope', 50);
            $table->json('requested_permissions');
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled']);
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');

            $table->index('account_id');
            $table->index(['account_id', 'status']);

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_requests');
        Schema::dropIfExists('account_dashboard_preferences');
    }
};
