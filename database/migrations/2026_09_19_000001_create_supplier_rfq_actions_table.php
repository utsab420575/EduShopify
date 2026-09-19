<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_rfq_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('rfq_id');
            $table->enum('action_type', [
                'viewed',
                'interested',
                'not_interested',
                'messaged',
                'preparing_quote',
                'quoted',
            ]);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['rfq_id', 'supplier_account_id']);
            $table->index(['supplier_account_id', 'action_type']);
            $table->index(['rfq_id', 'action_type']);

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_rfq_actions');
    }
};
