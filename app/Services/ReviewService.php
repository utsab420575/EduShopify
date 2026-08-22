<?php

namespace App\Services;

use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Rule: a review is allowed once a supplier has submitted a quotation
     * (review_context = quotation_experience) or once a purchase order has
     * completed (review_context = purchase_experience). One review per
     * buyer per quotation/PO in that context — enforced by a unique index.
     */
    public function reviewForQuotation(Account $buyerAccount, User $user, Quotation $quotation, int $rating, ?string $title, ?string $comment): Review
    {
        if ($quotation->status === 'draft') {
            throw ValidationException::withMessages(['rating' => 'You can only review a quotation once it has been submitted.']);
        }

        if (Review::where('buyer_account_id', $buyerAccount->id)
            ->where('quotation_id', $quotation->id)
            ->where('review_context', 'quotation_experience')
            ->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this quotation.']);
        }

        return Review::create([
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
    }

    public function reviewForPurchaseOrder(Account $buyerAccount, User $user, PurchaseOrder $purchaseOrder, int $rating, ?string $title, ?string $comment): Review
    {
        if (! $purchaseOrder->isCompleted()) {
            throw ValidationException::withMessages(['rating' => 'You can only review a purchase order once it is completed.']);
        }

        if (Review::where('buyer_account_id', $buyerAccount->id)
            ->where('purchase_order_id', $purchaseOrder->id)
            ->where('review_context', 'purchase_experience')
            ->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this purchase order.']);
        }

        return Review::create([
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
    }
}
