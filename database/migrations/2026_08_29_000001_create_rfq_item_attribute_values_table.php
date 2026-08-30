<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * rfq_item_attribute_values — structured, category-defined specification
 * values for an RFQ item, mirroring listing_attribute_values exactly (same
 * shape, same lack of a DB-level unique constraint — uniqueness is enforced
 * in application code via updateOrCreate on rfq_item_id+attribute_id, same
 * as the listing side). rfq_items.specs remains for free-form extras.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_item_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_item_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('custom_value')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->index(['rfq_item_id', 'attribute_id']);

            $table->foreign('rfq_item_id')->references('id')->on('rfq_items')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_item_attribute_values');
    }
};
