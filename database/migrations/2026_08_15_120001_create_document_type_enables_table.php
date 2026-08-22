<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_type_enables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('capability_type_id');
            $table->timestamps();

            $table->unique(['document_type_id', 'capability_type_id']);

            $table->foreign('document_type_id')->references('id')->on('document_types')->cascadeOnDelete();
            $table->foreign('capability_type_id')->references('id')->on('capability_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_enables');
    }
};
