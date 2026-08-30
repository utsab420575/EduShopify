<?php

namespace App\Policies;

use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RfqPolicy
{
    use HandlesAuthorization;

    /**
     * Common capability + permission check.
     */
    private function checkBuyerAccess(User $user, string $permission): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        // Capability must be ACTIVE (business requirement)
        if (! $account->hasActiveCapability('buyer')) {
            return false;
        }

        // Spatie permission check in active team context
        return $user->hasPermissionTo($permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->checkBuyerAccess($user, 'rfq.view');
    }

    public function view(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.view')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id;
    }

    public function create(User $user): bool
    {
        return $this->checkBuyerAccess($user, 'rfq.create');
    }

    public function updateDraft(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.update_draft')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id
            && $rfq->status === 'draft';
    }

    /**
     * Version-aware editing: a draft edits freely, an open RFQ may still be
     * edited but goes through RfqService's versioned-update path instead of
     * a silent overwrite.
     */
    public function update(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.update_draft')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id
            && in_array($rfq->status, ['draft', 'open'], true);
    }

    public function extendDeadline(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.update_draft')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id
            && $rfq->status === 'open';
    }

    public function publish(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.publish')) {
            return false;
        }

        // pending_approval means it's already awaiting Admin review (see
        // rfq_requires_admin_approval) — not re-publishable by the buyer.
        return $rfq->buyer_account_id === $user->accountMember?->account_id
            && $rfq->status === 'draft';
    }

    public function cancel(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'rfq.cancel')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id
            && ! in_array($rfq->status, ['cancelled', 'completed']);
    }

    public function compare(User $user, Rfq $rfq): bool
    {
        if (! $this->checkBuyerAccess($user, 'quotation.compare')) {
            return false;
        }

        return $rfq->buyer_account_id === $user->accountMember?->account_id;
    }

    /* ── Supplier side: RFQ opportunities ──────────────────────────────── */

    private function checkSupplierAccess(User $user, string $permission): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        if (! $account->hasActiveCapability('supplier')) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * True once the supplier's rfq_supplier_queue row for this RFQ is both
     * eligible and past its subscription-plan delay — the same gate that
     * governs quotation submission and question-asking.
     */
    public function viewAsOpportunity(User $user, Rfq $rfq): bool
    {
        if (! $this->checkSupplierAccess($user, 'rfq_opportunity.view')) {
            return false;
        }

        $accountId = $user->accountMember?->account_id;

        return RfqSupplierQueue::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $accountId)
            ->released()
            ->exists();
    }

    public function askQuestion(User $user, Rfq $rfq): bool
    {
        if (! $this->checkSupplierAccess($user, 'rfq_question.create')) {
            return false;
        }

        if ($rfq->qnaClosed() || ! $rfq->isOpen()) {
            return false;
        }

        return $this->viewAsOpportunity($user, $rfq);
    }

    /**
     * Declining an opportunity reuses rfq_opportunity.view rather than a new
     * permission — a supplier who can see the opportunity can decide not to
     * respond to it.
     */
    public function decline(User $user, Rfq $rfq): bool
    {
        return $rfq->acceptsQuotations() && $this->viewAsOpportunity($user, $rfq);
    }
}
