<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // accounts — primary_owner_user_id added as nullable FK after users exists
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 50)->unique();
            $table->enum('account_type', ['individual', 'organization'])->default('individual');
            $table->boolean('is_system_account')->default(false);
            $table->string('display_name', 200);
            $table->string('slug', 180)->unique()->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'suspended', 'deletion_pending', 'deleted'])
                  ->default('draft');
            $table->unsignedBigInteger('primary_owner_user_id')->nullable();
            $table->timestamp('converted_from_individual_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->unsignedBigInteger('suspended_by_user_id')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by_user_id')->nullable();
            $table->text('deletion_reason')->nullable();
            $table->timestamps();

            $table->index('account_type');
            $table->index('is_system_account');
            $table->index('status');
            $table->index('primary_owner_user_id');

            // Foreign keys to users — added here since users table precedes accounts
            $table->foreign('primary_owner_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('suspended_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
