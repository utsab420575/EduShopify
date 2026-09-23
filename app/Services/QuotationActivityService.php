<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationActivity;

/**
 * Records the quotation_activities timeline. actor_role ('supplier' | 'buyer' | 'system') + user_id
 * provides explicit, fast, and expressive role tracking without heavy polymorphic joins.
 */
class QuotationActivityService
{
    public function record(
        Quotation $quotation,
        string $activityType,
        ?string $message = null,
        ?string $actorRole = null,
        ?int $userId = null
    ): QuotationActivity {
        $quotation->loadMissing('rfq');

        $role = $actorRole ?: match ($activityType) {
            'viewed_by_buyer', 'buyer_messaged', 'buyer_requested_revision', 'shortlisted', 'awarded', 'award_cancelled', 'rejected' => 'buyer',
            'drafted', 'submitted', 'unsubmitted', 'quotation_updated', 'supplier_replied', 'accepted', 'award_declined', 'rejected_by_supplier' => 'supplier',
            'expired' => 'system',
            default => 'system',
        };

        $resolvedUserId = $userId ?: (auth()->check() ? auth()->id() : null);

        return QuotationActivity::create([
            'quotation_id'        => $quotation->id,
            'actor_role'          => $role,
            'user_id'             => $resolvedUserId,
            'rfq_id'              => $quotation->rfq_id,
            'supplier_account_id' => $quotation->supplier_account_id,
            'buyer_account_id'    => $quotation->rfq?->buyer_account_id,
            'activity_type'       => $activityType,
            'message'             => $message,
        ]);
    }
}
