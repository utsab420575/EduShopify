<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 50)->unique();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->unsignedInteger('rfq_version_no');
            $table->unsignedInteger('current_revision_no')->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_charge', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('currency_code', 10)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('support_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->longText('proposal')->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'revision_requested', 'revised', 'withdrawn', 'shortlisted', 'rejected', 'awarded', 'expired'])
                  ->default('draft');
            $table->text('rejection_comment')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('buyer_viewed_at')->nullable();
            $table->unsignedBigInteger('decided_by_user_id')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('revised_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rfq_id', 'supplier_account_id']);

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('decided_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('rfq_item_id')->nullable();
            $table->unsignedBigInteger('offered_listing_id')->nullable();
            $table->unsignedBigInteger('offered_variant_id')->nullable();
            $table->boolean('is_alternative')->default(false);
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('custom_unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 7, 4)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->json('specs')->nullable();
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('rfq_item_id')->references('id')->on('rfq_items')->nullOnDelete();
            $table->foreign('offered_listing_id')->references('id')->on('listings')->nullOnDelete();
            $table->foreign('offered_variant_id')->references('id')->on('listing_variants')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('quotation_revision_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('requested_by_account_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->text('requested_changes');
            $table->json('requested_fields')->nullable();
            $table->timestamp('response_deadline')->nullable();
            $table->enum('status', ['pending', 'accepted', 'revised', 'declined', 'expired', 'cancelled'])->default('pending');
            $table->text('supplier_response')->nullable();
            $table->unsignedBigInteger('responded_by_user_id')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('requested_by_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('responded_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedInteger('revision_no');
            $table->unsignedInteger('rfq_version_no');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->decimal('shipping_charge', 15, 2);
            $table->decimal('grand_total', 15, 2);
            $table->string('currency_code', 10)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('support_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->longText('proposal')->nullable();
            $table->text('change_summary')->nullable();
            $table->unsignedBigInteger('created_by_account_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->unique(['quotation_id', 'revision_no']);
            $table->index('quotation_id');
            $table->index('created_by_account_id');

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('created_by_account_id')->references('id')->on('accounts')->restrictOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('quotation_revision_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_revision_id');
            $table->unsignedBigInteger('rfq_item_id')->nullable();
            $table->unsignedBigInteger('offered_listing_id')->nullable();
            $table->unsignedBigInteger('offered_variant_id')->nullable();
            $table->boolean('is_alternative')->default(false);
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('custom_unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 7, 4)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->json('specs')->nullable();
            $table->timestamps();

            $table->index('quotation_revision_id');
            $table->index('rfq_item_id');
            $table->index('offered_listing_id');

            $table->foreign('quotation_revision_id')->references('id')->on('quotation_revisions')->cascadeOnDelete();
            $table->foreign('rfq_item_id')->references('id')->on('rfq_items')->nullOnDelete();
            $table->foreign('offered_listing_id')->references('id')->on('listings')->nullOnDelete();
            $table->foreign('offered_variant_id')->references('id')->on('listing_variants')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('rfq_shortlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('buyer_account_id');
            $table->unsignedBigInteger('shortlisted_by_user_id');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'quotation_id']);

            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('buyer_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('shortlisted_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_shortlists');
        Schema::dropIfExists('quotation_revision_items');
        Schema::dropIfExists('quotation_revisions');
        Schema::dropIfExists('quotation_revision_requests');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
