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
        Schema::dropIfExists('subscription_plan_benefits');

        Schema::create('subscription_plan_benefits', function (Blueprint $table) {
            // Foreign keys need InnoDB — never rely on the server's default engine
            // (a MyISAM default makes every FK to this table fail with errno 150).
            $table->engine('InnoDB');

            $table->bigIncrements('id');

            $table->unsignedBigInteger('subscription_plan_id')->index();

            $table->foreignId('subscription_benefit_id')
                ->constrained('subscription_benefits')
                ->cascadeOnDelete();

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
