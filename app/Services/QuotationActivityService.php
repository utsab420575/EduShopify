<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationActivity;

/**
 * Records the quotation_activities timeline. quotations.status remains the
 * single source of truth for the quotation's CURRENT state — this service
 * only ever appends history rows for the supplier "My Quotations" timeline
 * and the buyer's quotation activity view.
 */
class QuotationActivityService
{
    public function record(Quotation $quotation, string $activityType, ?string $message = null): QuotationActivity
    {
        $quotation->loadMissing('rfq');

        return QuotationActivity::create([
            'quotation_id'        => $quotation->id,
            'rfq_id'              => $quotation->rfq_id,
            'supplier_account_id' => $quotation->supplier_account_id,
            'buyer_account_id'    => $quotation->rfq->buyer_account_id,
            'activity_type'       => $activityType,
            'message'             => $message,
        ]);
    }
}
