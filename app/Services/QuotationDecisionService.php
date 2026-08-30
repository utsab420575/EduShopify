<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Quotation;
use App\Models\QuotationRevisionRequest;
use App\Models\RfqShortlist;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Buyer-side decisions on a received quotation. Awarding lives in
 * AwardService — it crosses into the award/purchase-order workflow.
 */
class QuotationDecisionService
{
    public function shortlist(Quotation $quotation, Account $buyerAccount, User $user, ?string $notes = null): RfqShortlist
    {
        $shortlist = RfqShortlist::updateOrCreate(
            ['rfq_id' => $quotation->rfq_id, 'quotation_id' => $quotation->id],
            [
                'buyer_account_id'       => $buyerAccount->id,
                'shortlisted_by_user_id' => $user->id,
                'notes'                  => $notes,
            ]
        );

        $this->notifySupplier($quotation, "Your quotation {$quotation->quotation_number} was shortlisted by the buyer.");

        return $shortlist;
    }

    public function removeFromShortlist(Quotation $quotation): void
    {
        RfqShortlist::where('rfq_id', $quotation->rfq_id)
            ->where('quotation_id', $quotation->id)
            ->delete();
    }

    public function requestRevision(Quotation $quotation, Account $buyerAccount, User $user, string $requestedChanges): QuotationRevisionRequest
    {
        return DB::transaction(function () use ($quotation, $buyerAccount, $user, $requestedChanges) {
            $request = QuotationRevisionRequest::create([
                'quotation_id'             => $quotation->id,
                'requested_by_account_id'  => $buyerAccount->id,
                'requested_by_user_id'     => $user->id,
                'requested_changes'        => $requestedChanges,
                'status'                   => 'pending',
            ]);

            $quotation->update(['status' => 'revision_requested']);

            $this->notifySupplier($quotation, "The buyer requested changes to your quotation {$quotation->quotation_number}.", route('supplier.quotations.revision.create', $quotation));

            return $request;
        });
    }

    public function reject(Quotation $quotation, string $reason): Quotation
    {
        if (in_array($quotation->status, ['rejected', 'withdrawn', 'awarded', 'expired'], true)) {
            throw ValidationException::withMessages(['status' => 'This quotation can no longer be rejected.']);
        }

        $quotation->update([
            'status'             => 'rejected',
            'rejection_comment'  => $reason,
            'rejected_at'        => now(),
        ]);

        $this->notifySupplier($quotation, "Your quotation {$quotation->quotation_number} was not selected by the buyer.");

        return $quotation;
    }

    public function markViewed(Quotation $quotation): void
    {
        if ($quotation->buyer_viewed_at === null) {
            $quotation->update(['buyer_viewed_at' => now()]);
        }
    }

    private function notifySupplier(Quotation $quotation, string $message, ?string $url = null): void
    {
        $users = User::whereHas('accountMember', fn ($q) => $q->where('account_id', $quotation->supplier_account_id)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message, $url ?? route('supplier.quotations.show', $quotation)));
        }
    }
}
