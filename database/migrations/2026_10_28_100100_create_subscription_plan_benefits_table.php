<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * subscription_plan_benefits — the value of each benefit on each plan; the
 * single source of truth for plan benefit configuration (replacing the
 * fixed max_* / has_* columns on subscription_plans once the application is
 * switched over).
 *
 * One row per plan + benefit. Deleting a plan removes its rows; a benefit in
 * use cannot be deleted (deactivate it via subscription_benefits.is_active).
 * Existing subscribers keep the values captured in
 * subscriptions.features_snapshot until renewal or plan change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_benefits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->foreignId('subscription_benefit_id')
                ->constrained('subscription_benefits')
                ->restrictOnDelete();

            $table->json('value');

            $table->timestamps();

            $table->unique(
                ['subscription_plan_id', 'subscription_benefit_id'],
                'unique_plan_benefit'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_benefits');
    }
};
