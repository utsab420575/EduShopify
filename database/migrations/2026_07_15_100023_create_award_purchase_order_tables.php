<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('award_number', 50)->unique();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('quotation_id')->unique();
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('awarded_by_user_id');
            $table->unsignedSmallInteger('award_attempt_no')->default(1);
            $table->enum('status', ['pending_supplier_response', 'accepted', 'rejected_by_supplier', 'cancelled', 'superseded'])
                  ->default('pending_supplier_response');
            $table->text('award_note')->nullable();
            $table->text('supplier_response_note')->nullable();
            $table->text('supplier_rejection_reason')->nullable();
            $table->timestamp('response_deadline');
            $table->timestamp('awarded_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'award_attempt_no']);

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('awarded_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->unsignedBigInteger('award_id')->unique();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_charge', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->string('currency_code', 10);
            $table->enum('status', ['issued', 'confirmed', 'in_progress', 'ready_for_delivery', 'delivered', 'completed', 'cancelled', 'disputed'])
                  ->default('issued');
            $table->text('payment_method_note')->nullable();
            $table->enum('payment_status', ['not_recorded', 'unpaid', 'partially_paid', 'paid'])->default('not_recorded');
            $table->timestamp('issued_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->foreign('award_id')->references('id')->on('awards')->cascadeOnDelete();
            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('quotation_item_id')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('custom_unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('purchase_order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->unsignedBigInteger('changed_by_account_id');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('changed_by_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_status_history');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('awards');
    }
};
