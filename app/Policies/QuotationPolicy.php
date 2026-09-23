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

    /**
     * Choosing which offer (within a Product Response) should be used if
     * this quotation is awarded — a preparatory action, so it shares
     * award()'s ownership/permission/status gate but not its "no pending
     * award already in flight" exclusivity check (selecting doesn't create
     * anything).
     */
    public function selectOffer(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'buyer', 'quotation.award') !== null
            && $this->ownsAsBuyer($user, $quotation)
            && in_array($quotation->status, ['submitted', 'revised', 'shortlisted', 'under_review'], true);
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

    /**
     * Usage: $this->authorize('selectProducts', [Quotation::class, $rfq]);
     * Allows selecting marketplace products if the supplier can either create a new quotation
     * for this RFQ, or is currently editing an active draft / revision of their existing quotation.
     */
    public function selectProducts(User $user, Rfq $rfq): bool
    {
        $account = $this->checkAccess($user, 'supplier', 'quotation.create')
            ?? $this->checkAccess($user, 'supplier', 'quotation.submit');

        if (! $account) {
            return false;
        }

        if (! $rfq->acceptsQuotations()) {
            return false;
        }

        $existing = $rfq->quotations()->where('supplier_account_id', $account->id)->first();
        if (! $existing) {
            return true;
        }

        return $this->ownsAsSupplier($user, $existing)
            && in_array($existing->status, ['draft', 'revision_requested'], true);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        $versionChanged = $quotation->rfq && $quotation->rfq_version_no !== $quotation->rfq->current_version_no;
        $canReviseStatus = in_array($quotation->status, ['draft', 'revision_requested'], true)
            || ($versionChanged && in_array($quotation->status, ['submitted', 'under_review', 'revised', 'shortlisted'], true));

        return $this->checkAccess($user, 'supplier', 'quotation.revise') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && $canReviseStatus
            && $quotation->rfq->acceptsQuotations();
    }

    /**
     * Editing an unsubmitted draft in place — distinct from update() above,
     * which now (in practice) only ever governs the revision_requested path;
     * a draft that has never been submitted has no revision yet to protect.
     */
    public function editDraft(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'supplier', 'quotation.create') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && $quotation->status === 'draft';
    }

    /**
     * Promoting a draft to submitted — the show-page "Submit Quotation" action.
     */
    public function submitDraft(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'supplier', 'quotation.submit') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && $quotation->status === 'draft'
            && $quotation->rfq->acceptsQuotations();
    }

    public function withdraw(User $user, Quotation $quotation): bool
    {
        return $this->checkAccess($user, 'supplier', 'quotation.withdraw') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && ! in_array($quotation->status, ['withdrawn', 'awarded', 'rejected', 'expired', 'draft'], true);
    }

    /**
     * Pulling a submitted quotation back to draft — same reachable-status
     * gate as withdraw() (any live, non-terminal status), just the opposite
     * destination. Reuses the 'quotation.submit' permission since it's the
     * direct inverse of that action.
     */
    public function undoSubmit(User $user, Quotation $quotation): bool
    {
        $hasActiveAward = $quotation->status === 'awarded'
            || $quotation->award()->whereIn('status', ['pending_supplier_response', 'accepted'])->exists()
            || ($quotation->rfq && $quotation->rfq->awards()->whereIn('status', ['pending_supplier_response', 'accepted'])->exists());

        return $this->checkAccess($user, 'supplier', 'quotation.submit') !== null
            && $this->ownsAsSupplier($user, $quotation)
            && ! in_array($quotation->status, ['withdrawn', 'awarded', 'rejected', 'expired', 'draft'], true)
            && ! $hasActiveAward;
    }
}
