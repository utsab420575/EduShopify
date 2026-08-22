<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->enum('review_context', ['quotation_experience', 'purchase_experience']);
            $table->unsignedBigInteger('rfq_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'published', 'hidden', 'flagged', 'rejected'])->default('pending');
            $table->unsignedBigInteger('moderated_by_user_id')->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['buyer_account_id', 'quotation_id', 'review_context']);
            $table->unique(['buyer_account_id', 'purchase_order_id', 'review_context']);

            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('rfq_id')->references('id')->on('rfqs')->nullOnDelete();
            $table->foreign('quotation_id')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $table->foreign('moderated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('replied_by_user_id');
            $table->text('reply');
            $table->enum('status', ['published', 'hidden'])->default('published');
            $table->unsignedBigInteger('moderated_by_user_id')->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'supplier_account_id']);
            $table->index('supplier_account_id');
            $table->index('status');

            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('replied_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('moderated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('reported_by_account_id');
            $table->unsignedBigInteger('reported_by_user_id');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed', 'action_taken'])->default('pending');
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('review_action')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'reported_by_account_id']);
            $table->index('review_id');
            $table->index('status');

            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('reported_by_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('reported_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('assigned_admin_user_id')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('subject');
            $table->longText('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'pending', 'answered', 'resolved', 'closed'])->default('open');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_admin_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sender_account_id')->nullable();
            $table->unsignedBigInteger('sender_user_id');
            $table->longText('message');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->foreign('sender_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('sender_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('saved_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('saved_by_user_id');
            $table->enum('visibility', ['personal', 'account'])->default('personal');
            $table->enum('item_type', ['listing', 'supplier', 'rfq', 'quotation']);
            $table->unsignedBigInteger('item_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['saved_by_user_id', 'item_type', 'item_id']);

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('saved_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_number', 50)->unique();
            $table->unsignedBigInteger('supplier_account_id')->nullable();
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->string('organization', 200)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'spam', 'closed'])->default('new');
            $table->unsignedBigInteger('handled_by_user_id')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['supplier_account_id', 'status']);
            $table->index('listing_id');
            $table->index('status');
            $table->index('email');
            $table->index('created_at');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();
            $table->foreign('handled_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
        Schema::dropIfExists('saved_items');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('review_replies');
        Schema::dropIfExists('reviews');
    }
};
