<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * subscription_benefits — catalogue of every facility a subscription can
 * grant (featured_supplier, featured_listing, rfq_delay_minutes,
 * max_active_listings, priority_support, ...).
 *
 * Values live in subscription_plan_benefits (per plan) and
 * account_benefit_overrides (per account); default_value is the fallback
 * when neither defines one.
 *
 * value_type is a plain string (boolean | integer | decimal | text),
 * validated in the application rather than as a DB enum, so new types never
 * need a driver-specific ALTER.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_benefits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('value_type', 20)->default('boolean');
            $table->json('default_value')->nullable();
            $table->string('unit', 30)->nullable();          // display only, e.g. "minutes", "listings"
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_benefits');
    }
};
