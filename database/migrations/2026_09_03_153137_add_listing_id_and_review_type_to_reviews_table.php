<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable()->after('supplier_account_id');
            $table->enum('review_type', ['supplier', 'product'])->default('supplier')->after('listing_id');

            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();

            // Only bite for product reviews (listing_id set) — MySQL unique
            // indexes never conflict on NULL, so existing supplier rows
            // (listing_id always NULL) are untouched by these. Scopes "one
            // product review per buyer per listing per order."
            $table->unique(['buyer_account_id', 'listing_id', 'quotation_id'], 'reviews_buyer_listing_quotation_unique');
            $table->unique(['buyer_account_id', 'listing_id', 'purchase_order_id'], 'reviews_buyer_listing_po_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_buyer_listing_quotation_unique');
            $table->dropUnique('reviews_buyer_listing_po_unique');
            $table->dropForeign(['listing_id']);
            $table->dropColumn(['listing_id', 'review_type']);
        });
    }
};
