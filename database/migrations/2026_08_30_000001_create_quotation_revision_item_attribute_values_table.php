<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * quotation_revision_item_attribute_values — immutable per-revision snapshot
 * of a quotation item's offered specifications, mirroring
 * quotation_item_attribute_values exactly but keyed to a
 * quotation_revision_item instead of a live quotation_item. Without this, a
 * later revision would silently lose the specs the buyer originally saw on
 * an earlier revision (spec §31).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_revision_item_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_revision_item_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('custom_value')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->index(['quotation_revision_item_id', 'attribute_id'], 'qriav_revision_item_attr_idx');

            $table->foreign('quotation_revision_item_id', 'qriav_revision_item_fk')
                ->references('id')->on('quotation_revision_items')->cascadeOnDelete();
            $table->foreign('attribute_id', 'qriav_attribute_fk')->references('id')->on('attributes')->cascadeOnDelete();
            $table->foreign('attribute_value_id', 'qriav_attribute_value_fk')->references('id')->on('attribute_values')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_revision_item_attribute_values');
    }
};
