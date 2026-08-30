<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Rfq;
use App\Models\RfqQuestion;
use App\Models\RfqSupplierQueue;
use App\Models\User;

class RfqOpportunityService
{
    public function markSeen(Rfq $rfq, Account $supplierAccount): void
    {
        RfqSupplierQueue::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $supplierAccount->id)
            ->whereNull('seen_at')
            ->update(['seen_at' => now(), 'status' => 'seen']);
    }

    public function askQuestion(Rfq $rfq, Account $supplierAccount, User $user, string $question, bool $isPublic = true): RfqQuestion
    {
        return RfqQuestion::create([
            'rfq_id'              => $rfq->id,
            'supplier_account_id' => $supplierAccount->id,
            'asked_by_user_id'    => $user->id,
            'question'            => $question,
            'is_public'           => $isPublic,
            'status'              => 'pending',
        ]);
    }

    /**
     * Supplier chooses not to participate (spec §33). The queue row is kept
     * (never deleted) so eligibility/history stays intact — only its status
     * and an optional reason change.
     */
    public function decline(Rfq $rfq, Account $supplierAccount, ?string $reason = null): void
    {
        RfqSupplierQueue::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $supplierAccount->id)
            ->update(['status' => 'ignored', 'decline_reason' => $reason]);
    }
}
