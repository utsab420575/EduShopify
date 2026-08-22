<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    /**
     * Resolves the active account for a capability + permission pair, or null
     * if either check fails. Shared by both the buyer (received quotations)
     * and supplier (own quotations) sides of this model.
     */
    private function checkAccess(User $user, string $capability, string $permission): ?Account
    {
        if (! $user->isActive()) {
            return null;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return null;
        }

        if (! $account->hasActiveCapability($capability)) {
            return null;
        }

        return $user->hasPermissionTo($permission) ? $account : null;
    }

    private function ownsAsBuyer(User $user, Quotation $quotation): bool
    {
        return $quotation->rfq->buyer_account_id === $user->accountMember?->account_id;
    }

    private function ownsAsSupplier(User $user, Quotation $quotation): bool
    {
        return $quotation->supplier_account_id === $user->accountMember?->account_id;
    }

    /* ── Buyer side: viewing/deciding on received quotations ─────────────── */

    public function viewAny(User $user): bool
    {
        return $this->checkAccess($user, 'buyer', 'quotation.view_received') !== null;
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($this->ownsAsBuyer($user, $quotation)) {
            return $this->checkAccess($user, 'buyer', 'quotation.view_received') !== null;
        }

        if ($this->ownsAsSupplier($user, $quotation)) {
            return $this->checkAccess($user, 'supplier', 'quotation.view_own') !== null;
        }

        return false;
    }

    public function shortlist(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'buyer', 'quotation.shortlist') !== null
            && $this->ownsAsBuyer($user, $quotation)
            && in_array($quotation->status, ['submitted', 'under_review', 'revised'], true);
    }

    public function requestRevision(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'buyer', 'quotation.request_revision') !== null
            && $this->ownsAsBuyer($user, $quotation)
            && in_array($quotation->status, ['submitted', 'under_review', 'shortlisted', 'revised'], true);
    }

    public function reject(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'buyer', 'quotation.reject') !== null
            && $this->ownsAsBuyer($user, $quotation)
            && ! in_array($quotation->status, ['rejected', 'withdrawn', 'awarded', 'expired', 'draft'], true);
    }

    public function award(User $user, Quotation $quotation): bool
    {
        if ($this->checkAccess($user, 'buyer', 'quotation.award') === null || ! $this->ownsAsBuyer($user, $quotation)) {
            return false;
        }

        if (! in_array($quotation->status, ['submitted', 'revised', 'shortlisted', 'under_review'], true)) {
            return false;
        }

        // Only one non-rejected/non-cancelled award may be active per RFQ.
        return ! $quotation->rfq->awards()->where('status', 'pending_supplier_response')->exists();
    }

    /* ── Supplier side: submitting/managing own quotations ───────────────── */

    /**
     * Usage: $this->authorize('create', [Quotation::class, $rfq]);
     * Eligibility (subscription, delay, capability) is enforced by RfqPolicy::viewAsOpportunity —
     * a supplier can only reach the submit form for an RFQ they're already eligible to see.
     */
    public function create(User $user, Rfq $rfq): bool
    {
        $account = $this->checkAccess($user, 'supplier', 'quotation.submit');

        if (! $account) {
            return false;
        }

        if (! $rfq->acceptsQuotations()) {
            return false;
        }

        // One live quotation per supplier per RFQ — further attempts are revisions, not creates.
        return ! $rfq->quotations()->where('supplier_account_id', $account->id)->exists();
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'supplier', 'quotation.revise') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && in_array($quotation->status, ['draft', 'revision_requested'], true)
            && $quotation->rfq->acceptsQuotations();
    }

    public function withdraw(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'supplier', 'quotation.withdraw') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && ! in_array($quotation->status, ['withdrawn', 'awarded', 'rejected', 'expired', 'draft'], true);
    }
}
