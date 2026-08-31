<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Mirrors supplier_gallery exactly — same shape, buyer-scoped. */
    public function up(): void
    {
        Schema::create('buyer_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('image_path')->nullable();
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->index(['buyer_account_id', 'is_active', 'sort_order']);

            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_galleries');
    }
};
