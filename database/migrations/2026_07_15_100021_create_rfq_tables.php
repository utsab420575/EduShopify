<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number', 50)->unique();
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->enum('visibility_type', ['global', 'selected_suppliers'])->default('global');
            $table->unsignedBigInteger('source_listing_id')->nullable();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->unsignedBigInteger('delivery_country_id')->nullable();
            $table->unsignedBigInteger('delivery_state_id')->nullable();
            $table->unsignedBigInteger('delivery_city_id')->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->boolean('allow_partial_quotation')->default(true);
            $table->boolean('allow_alternative_products')->default(true);
            $table->timestamp('quotation_deadline');
            $table->timestamp('qna_deadline')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'open', 'closed', 'award_pending', 'awarded', 'cancelled', 'expired', 'completed'])
                  ->default('draft');
            $table->unsignedInteger('current_version_no')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('items_count')->default(0);
            $table->unsignedInteger('quotations_count')->default(0);
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('source_listing_id')->references('id')->on('listings')->nullOnDelete();
            $table->foreign('delivery_country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('delivery_state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('delivery_city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->enum('item_type', ['product', 'service']);
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('custom_unit', 50)->nullable();
            $table->decimal('estimated_unit_price', 15, 2)->nullable();
            $table->json('specs')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('rfq_selected_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('selected_by_user_id');
            $table->timestamp('invited_at')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_account_id']);

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('selected_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('rfq_target_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });

        Schema::create('rfq_supplier_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->enum('source', ['global', 'selected_supplier']);
            $table->unsignedInteger('delay_minutes')->default(0);
            $table->timestamp('available_at');
            $table->enum('eligibility_status', ['eligible', 'not_approved', 'no_subscription', 'subscription_expired', 'blocked', 'expired'])
                  ->default('eligible');
            $table->json('eligibility_snapshot')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->enum('status', ['pending', 'available', 'notified', 'seen', 'ignored', 'quotation_submitted', 'expired'])
                  ->default('pending');
            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_account_id']);

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
        });

        Schema::create('rfq_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('asked_by_user_id');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->unsignedBigInteger('answered_by_user_id')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->enum('status', ['pending', 'answered', 'hidden'])->default('pending');
            $table->unsignedBigInteger('hidden_by_user_id')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->text('hidden_reason')->nullable();
            $table->timestamps();

            $table->index(['rfq_id', 'status']);
            $table->index('supplier_account_id');
            $table->index('asked_by_user_id');

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('asked_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('answered_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('hidden_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('rfq_deadline_extensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('extended_by_user_id');
            $table->enum('deadline_type', ['quotation', 'qna']);
            $table->timestamp('old_deadline');
            $table->timestamp('new_deadline');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('rfq_id');
            $table->index('deadline_type');

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('extended_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('rfq_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedInteger('from_version_no');
            $table->unsignedInteger('to_version_no');
            $table->enum('change_level', ['minor', 'major']);
            $table->json('changed_fields');
            $table->json('old_snapshot');
            $table->json('new_snapshot');
            $table->boolean('requires_quotation_revision')->default(true);
            $table->unsignedBigInteger('changed_by_user_id');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_change_logs');
        Schema::dropIfExists('rfq_deadline_extensions');
        Schema::dropIfExists('rfq_questions');
        Schema::dropIfExists('rfq_supplier_queue');
        Schema::dropIfExists('rfq_target_filters');
        Schema::dropIfExists('rfq_selected_suppliers');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};
