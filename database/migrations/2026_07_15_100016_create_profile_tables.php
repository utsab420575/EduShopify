<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->unsignedBigInteger('buyer_type_id')->nullable();
            $table->string('display_name', 200);
            $table->string('organization_name', 200)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->text('bio')->nullable();
            $table->text('procurement_info')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('buyer_type_id')->references('id')->on('buyer_types')->nullOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });

        Schema::create('supplier_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->string('slug', 180)->unique()->nullable();
            $table->string('display_name', 200);
            $table->string('legal_name', 200)->nullable();
            $table->string('company_type', 100)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('support_email', 150)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->unsignedInteger('employees')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('profile_photo')->nullable();
            $table->longText('description')->nullable();
            $table->json('socials')->nullable();
            $table->decimal('rating', 4, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('quotation_response_rate', 5, 2)->default(0.00);
            $table->unsignedInteger('average_response_minutes')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_profiles');
        Schema::dropIfExists('buyer_profiles');
    }
};
