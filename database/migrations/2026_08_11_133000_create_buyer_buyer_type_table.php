<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_buyer_type', function (Blueprint $table) {
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('buyer_type_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['buyer_account_id', 'buyer_type_id']);

            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('buyer_type_id')->references('id')->on('buyer_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_buyer_type');
    }
};
