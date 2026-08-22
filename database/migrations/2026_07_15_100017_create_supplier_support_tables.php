<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->enum('location_type', ['primary', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery']);
            $table->string('label', 150)->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_provider', 50)->nullable();
            $table->string('provider_place_id')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('supplier_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->enum('area_level', ['country', 'state', 'city', 'radius']);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->decimal('radius_km', 10, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('supplier_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('document_type_id');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['supplier_account_id', 'document_type_id']);
            $table->index('status');
            $table->index('expires_at');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->restrictOnDelete();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('verified_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('supplier_gallery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('image_path')->nullable();
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->index(['supplier_account_id', 'is_active', 'sort_order']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('supplier_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->enum('provider', ['youtube', 'vimeo', 'other'])->default('youtube');
            $table->string('video_id', 100)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->index(['supplier_account_id', 'is_active', 'sort_order']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('account_location_id')->nullable();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun … 6=Sat
            $table->boolean('is_open')->default(true);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamps();

            $table->unique(['supplier_account_id', 'account_location_id', 'day_of_week'], 'business_hours_supp_loc_day_unique');
            $table->index('supplier_account_id');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('account_location_id')->references('id')->on('account_locations')->cascadeOnDelete();
        });

        Schema::create('supplier_supplier_type', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('supplier_type_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['supplier_account_id', 'supplier_type_id']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('supplier_type_id')->references('id')->on('supplier_types')->cascadeOnDelete();
        });

        Schema::create('exhibition_supplier', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('exhibition_id');
            $table->string('booth_number', 100)->nullable();
            $table->unsignedSmallInteger('participation_year')->nullable();
            $table->timestamps();

            $table->primary(['supplier_account_id', 'exhibition_id']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_supplier');
        Schema::dropIfExists('supplier_supplier_type');
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('supplier_videos');
        Schema::dropIfExists('supplier_gallery');
        Schema::dropIfExists('supplier_documents');
        Schema::dropIfExists('supplier_service_areas');
        Schema::dropIfExists('account_locations');
    }
};
