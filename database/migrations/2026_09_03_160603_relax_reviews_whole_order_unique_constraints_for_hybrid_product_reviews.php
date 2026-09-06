<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original (buyer_account_id, quotation_id, review_context) and
     * (buyer_account_id, purchase_order_id, review_context) unique indexes
     * predate review_type/listing_id and assumed exactly one review per
     * buyer per order. The hybrid review flow now writes one review_type
     * =supplier row plus N review_type=product rows (one per listing) for
     * the same buyer + quotation/purchase_order — those N product rows
     * legitimately share buyer_account_id/quotation_id/review_context, so
     * the old constraints must go or every fan-out after the first row
     * would violate them.
     *
     * Per-listing product-review uniqueness is already covered by
     * reviews_buyer_listing_quotation_unique / reviews_buyer_listing_po_unique
     * (added alongside listing_id/review_type). A composite index can't also
     * re-express "at most one supplier row per order" without listing_id in
     * the key — and listing_id is NULL on every supplier row, which exempts
     * NULL-listing_id rows from the constraint entirely under standard SQL
     * unique-index semantics (the same reasoning already noted on those two
     * indexes). So supplier-row-per-order singularity is now enforced at the
     * application level only — ReviewService::reviewForQuotation() /
     * reviewForPurchaseOrder() and ReviewPolicy::createForQuotation() /
     * createForPurchaseOrder() already scope their "already reviewed" checks
     * to review_type=supplier.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['buyer_account_id', 'quotation_id', 'review_context']);
            $table->dropUnique(['buyer_account_id', 'purchase_order_id', 'review_context']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['buyer_account_id', 'quotation_id', 'review_context']);
            $table->unique(['buyer_account_id', 'purchase_order_id', 'review_context']);
        });
    }
};
