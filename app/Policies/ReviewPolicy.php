<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    private function checkBuyerAccess(User $user, string $permission): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        if (! $account->hasActiveCapability('buyer')) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Usage: $this->authorize('createForQuotation', [Review::class, $quotation]);
     */
    public function createForQuotation(User $user, Quotation $quotation): bool
    {
        if (! $this->checkBuyerAccess($user, 'supplier.review')) {
            return false;
        }

        if ($quotation->rfq->buyer_account_id !== $user->accountMember?->account_id) {
            return false;
        }

        if ($quotation->status === 'draft') {
            return false;
        }

        return ! Review::where('buyer_account_id', $user->accountMember?->account_id)
            ->where('quotation_id', $quotation->id)
            ->where('review_context', 'quotation_experience')
            ->exists();
    }

    public function createForPurchaseOrder(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! $this->checkBuyerAccess($user, 'supplier.review')) {
            return false;
        }

        if ($purchaseOrder->buyer_account_id !== $user->accountMember?->account_id) {
            return false;
        }

        if (! $purchaseOrder->isCompleted()) {
            return false;
        }

        return ! Review::where('buyer_account_id', $user->accountMember?->account_id)
            ->where('purchase_order_id', $purchaseOrder->id)
            ->where('review_context', 'purchase_experience')
            ->exists();
    }

    /**
     * Supplier side: one public reply per published review (unique(review_id, supplier_account_id)).
     */
    public function reply(User $user, Review $review): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive() || ! $account->hasActiveCapability('supplier')) {
            return false;
        }

        if (! $user->hasPermissionTo('review.reply')) {
            return false;
        }

        if ($review->supplier_account_id !== $account->id || ! $review->isPublished()) {
            return false;
        }

        return $review->reply === null;
    }
}
