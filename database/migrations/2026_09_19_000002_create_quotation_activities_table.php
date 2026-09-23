<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('buyer_account_id');
            $table->enum('activity_type', [
                'drafted',
                'submitted',
                'viewed_by_buyer',
                'buyer_messaged',
                'supplier_replied',
                'buyer_requested_revision',
                'quotation_updated',
                'accepted',
                'rejected',
                'expired',
            ]);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['quotation_id', 'created_at']);
            $table->index(['rfq_id', 'activity_type']);
            $table->index(['supplier_account_id', 'activity_type']);
            $table->index(['buyer_account_id', 'activity_type']);

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_activities');
    }
};
