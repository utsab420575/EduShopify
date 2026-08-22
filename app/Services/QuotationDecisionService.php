<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Quotation;
use App\Models\QuotationRevisionRequest;
use App\Models\RfqShortlist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Buyer-side decisions on a received quotation. Awarding lives in
 * AwardService — it crosses into the award/purchase-order workflow.
 */
class QuotationDecisionService
{
    public function shortlist(Quotation $quotation, Account $buyerAccount, User $user, ?string $notes = null): RfqShortlist
    {
        return RfqShortlist::updateOrCreate(
            ['rfq_id' => $quotation->rfq_id, 'quotation_id' => $quotation->id],
            [
                'buyer_account_id'       => $buyerAccount->id,
                'shortlisted_by_user_id' => $user->id,
                'notes'                  => $notes,
            ]
        );
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

        return $quotation;
    }

    public function markViewed(Quotation $quotation): void
    {
        if ($quotation->buyer_viewed_at === null) {
            $quotation->update(['buyer_viewed_at' => now()]);
        }
    }
}
