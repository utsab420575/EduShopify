<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * account_benefit_overrides — admin-set benefit values for one account,
 * independent of its subscription plan.
 *
 * An override REPLACES the plan value (it does not add to it); value
 * false/0 disables a benefit. It can grant a benefit without a paid
 * subscription, but account eligibility is still checked in the
 * application. starts_at / expires_at are optional (null = immediately /
 * never). Rows are kept for history — overlapping active overrides are
 * prevented in application logic, not by a unique constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('account_benefit_overrides');

        Schema::create('account_benefit_overrides', function (Blueprint $table) {
            // Foreign keys need InnoDB — never rely on the server's default engine
            // (a MyISAM default makes every FK to this table fail with errno 150).
            $table->engine('InnoDB');

            $table->bigIncrements('id');

            $table->unsignedBigInteger('account_id')->index();

            $table->foreignId('subscription_benefit_id')
                ->constrained('subscription_benefits')
                ->cascadeOnDelete();

            $table->json('value');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(
                ['account_id', 'subscription_benefit_id', 'is_active'],
                'account_benefit_override_lookup'
            );
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_benefit_overrides');
    }
};
