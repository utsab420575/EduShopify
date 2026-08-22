<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->enum('billing_type', ['free', 'monthly', 'yearly', 'custom']);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->string('currency_code', 10)->default('USD');
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedSmallInteger('bonus_days')->default(0);
            $table->unsignedInteger('max_active_listings')->default(0);
            $table->unsignedInteger('max_products')->default(0);
            $table->unsignedInteger('max_services')->default(0);
            $table->unsignedInteger('max_team_members')->default(1);
            $table->unsignedInteger('max_monthly_quotations')->default(0);
            $table->unsignedInteger('rfq_delay_minutes')->default(0);
            $table->boolean('has_rfq_notifications')->default(false);
            $table->boolean('has_analytics')->default(false);
            $table->boolean('has_verified_badge')->default(false);
            $table->boolean('has_homepage_placement')->default(false);
            $table->boolean('has_team_members')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_free')->default(false);
            $table->boolean('requires_supplier_approval')->default(true);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('is_free');
            $table->index('is_featured');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('selected_by_user_id');
            $table->enum('provider', ['stripe', 'manual', 'free'])->default('stripe');
            $table->string('provider_customer_id')->nullable();
            $table->string('provider_subscription_id')->nullable();
            $table->string('provider_session_id')->nullable();
            $table->string('plan_name_snapshot', 150)->nullable();  // V4.3
            $table->decimal('price_snapshot', 15, 2)->nullable();   // V4.3
            $table->json('features_snapshot')->nullable();           // V4.3
            $table->enum('status', ['pending', 'trialing', 'active', 'past_due', 'expired', 'cancelled', 'suspended'])
                  ->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->index(['supplier_account_id', 'status']);
            $table->index(['supplier_account_id', 'expires_at']);
            $table->index('provider_subscription_id');
            $table->index('current_period_end');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->restrictOnDelete();
            $table->foreign('selected_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->enum('provider', ['stripe', 'manual', 'free']);
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_invoice_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 10);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded', 'cancelled'])
                  ->default('pending');
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('subscription_id');
            $table->index(['supplier_account_id', 'status']);
            $table->index('provider_payment_id');
            $table->index('paid_at');

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
