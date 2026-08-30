<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * quotation_item_attribute_values — the supplier's structured offered
 * specifications for one quotation item, mirroring rfq_item_attribute_values
 * exactly so buyer-requested vs supplier-offered specs can be compared
 * attribute-by-attribute. No DB-level unique constraint — uniqueness is
 * enforced in application code via updateOrCreate on
 * quotation_item_id+attribute_id, same convention as the RFQ/listing side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_item_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_item_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('custom_value')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->index(['quotation_item_id', 'attribute_id'], 'qiav_item_attr_idx');

            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_item_attribute_values');
    }
};
