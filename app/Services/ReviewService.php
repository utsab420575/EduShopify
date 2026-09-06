<?php

namespace App\Services;

use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Rule: a review is allowed once a supplier has submitted a quotation
     * (review_context = quotation_experience) or once a purchase order has
     * completed (review_context = purchase_experience). One review per
     * buyer per quotation/PO in that context — enforced by a unique index.
     *
     * Hybrid write flow: the buyer's single rating/comment is stored as the
     * supplier review (review_type=supplier, as before) AND fanned out into
     * one review_type=product row per distinct listing on the order, all
     * starting out identical. The buyer can later edit any one product row
     * independently via updateProductReview() without touching the others.
     */
    public function reviewForQuotation(Account $buyerAccount, User $user, Quotation $quotation, int $rating, ?string $title, ?string $comment): Review
    {
        if ($quotation->status === 'draft') {
            throw ValidationException::withMessages(['rating' => 'You can only review a quotation once it has been submitted.']);
        }

        if (Review::where('buyer_account_id', $buyerAccount->id)
            ->where('quotation_id', $quotation->id)
            ->where('review_context', 'quotation_experience')
            ->where('review_type', 'supplier')
            ->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this quotation.']);
        }

        return DB::transaction(function () use ($buyerAccount, $user, $quotation, $rating, $title, $comment) {
            $review = Review::create([
                'buyer_account_id'    => $buyerAccount->id,
                'supplier_account_id' => $quotation->supplier_account_id,
                'created_by_user_id'  => $user->id,
                'review_context'      => 'quotation_experience',
                'rfq_id'              => $quotation->rfq_id,
                'quotation_id'        => $quotation->id,
                'rating'              => $rating,
                'title'               => $title,
                'comment'             => $comment,
                'status'              => 'pending',
            ]);

            $listingIds = $quotation->items()
                ->whereNotNull('offered_listing_id')
                ->distinct()
                ->pluck('offered_listing_id');

            $this->fanOutProductReviews($review, $listingIds);

            return $review;
        });
    }

    public function reviewForPurchaseOrder(Account $buyerAccount, User $user, PurchaseOrder $purchaseOrder, int $rating, ?string $title, ?string $comment): Review
    {
        if (! $purchaseOrder->isCompleted()) {
            throw ValidationException::withMessages(['rating' => 'You can only review a purchase order once it is completed.']);
        }

        if (Review::where('buyer_account_id', $buyerAccount->id)
            ->where('purchase_order_id', $purchaseOrder->id)
            ->where('review_context', 'purchase_experience')
            ->where('review_type', 'supplier')
            ->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this purchase order.']);
        }

        return DB::transaction(function () use ($buyerAccount, $user, $purchaseOrder, $rating, $title, $comment) {
            $review = Review::create([
                'buyer_account_id'    => $buyerAccount->id,
                'supplier_account_id' => $purchaseOrder->supplier_account_id,
                'created_by_user_id'  => $user->id,
                'review_context'      => 'purchase_experience',
                'rfq_id'              => $purchaseOrder->rfq_id,
                'quotation_id'        => $purchaseOrder->quotation_id,
                'purchase_order_id'   => $purchaseOrder->id,
                'rating'              => $rating,
                'title'               => $title,
                'comment'             => $comment,
                'status'              => 'pending',
            ]);

            $listingIds = $purchaseOrder->items()
                ->with('quotationItem:id,offered_listing_id')
                ->get()
                ->pluck('quotationItem.offered_listing_id')
                ->filter()
                ->unique();

            $this->fanOutProductReviews($review, $listingIds);

            return $review;
        });
    }

    /**
     * A buyer editing their opinion of one specific product from an order
     * they already reviewed. Independent row-level update — the sibling
     * supplier review and any other product reviews from the same order
     * are untouched. Re-enters moderation since the content changed.
     */
    public function updateProductReview(Account $buyerAccount, Review $review, int $rating, ?string $title, ?string $comment): Review
    {
        if ($review->review_type !== 'product' || $review->buyer_account_id !== $buyerAccount->id) {
            throw ValidationException::withMessages(['rating' => 'You cannot edit this review.']);
        }

        $review->update([
            'rating'                => $rating,
            'title'                 => $title,
            'comment'               => $comment,
            'status'                => 'pending',
            'published_at'          => null,
            'moderated_by_user_id'  => null,
            'moderation_reason'     => null,
        ]);

        return $review->fresh();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $listingIds
     */
    private function fanOutProductReviews(Review $supplierReview, $listingIds): void
    {
        foreach ($listingIds->unique()->values() as $listingId) {
            Review::create([
                'buyer_account_id'    => $supplierReview->buyer_account_id,
                'supplier_account_id' => $supplierReview->supplier_account_id,
                'listing_id'          => $listingId,
                'review_type'         => 'product',
                'created_by_user_id'  => $supplierReview->created_by_user_id,
                'review_context'      => $supplierReview->review_context,
                'rfq_id'              => $supplierReview->rfq_id,
                'quotation_id'        => $supplierReview->quotation_id,
                'purchase_order_id'   => $supplierReview->purchase_order_id,
                'rating'              => $supplierReview->rating,
                'title'               => $supplierReview->title,
                'comment'             => $supplierReview->comment,
                'status'              => 'pending',
            ]);
        }
    }
}
