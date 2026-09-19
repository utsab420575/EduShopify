<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Rfq;
use App\Models\SupplierRfqAction;
use Illuminate\Support\Collection;

/**
 * Records and queries the supplier_rfq_actions engagement log. Purely
 * additive tracking — never changes rfq_supplier_queue or quotation state.
 */
class SupplierRfqActionService
{
    public function record(Rfq $rfq, Account $supplierAccount, string $actionType, ?string $note = null): SupplierRfqAction
    {
        return SupplierRfqAction::create([
            'rfq_id'              => $rfq->id,
            'supplier_account_id' => $supplierAccount->id,
            'action_type'         => $actionType,
            'note'                => $note,
        ]);
    }

    /**
     * Most recent action row per action_type for this supplier on this RFQ —
     * the basis for "what state are the dynamic action buttons in" (spec's
     * Interested/Messaged/Preparing Quote/Quotation Submitted button states).
     */
    public function latestByType(Rfq $rfq, Account $supplierAccount): Collection
    {
        return SupplierRfqAction::forRfq($rfq->id)
            ->forSupplierAccount($supplierAccount->id)
            ->latest('id')
            ->get()
            ->unique('action_type')
            ->keyBy('action_type');
    }

    /**
     * Buyer-dashboard engagement overview for one RFQ: distinct supplier
     * counts per action_type (spec's "Buyer Dashboard RFQ Statistics").
     */
    public function engagementStatsForRfq(Rfq $rfq): array
    {
        $counts = SupplierRfqAction::forRfq($rfq->id)
            ->selectRaw('action_type, count(distinct supplier_account_id) as supplier_count')
            ->groupBy('action_type')
            ->pluck('supplier_count', 'action_type');

        return [
            'viewed'          => (int) ($counts['viewed'] ?? 0),
            'interested'      => (int) ($counts['interested'] ?? 0),
            'not_interested'  => (int) ($counts['not_interested'] ?? 0),
            'messaged'        => (int) ($counts['messaged'] ?? 0),
            'preparing_quote' => (int) ($counts['preparing_quote'] ?? 0),
            'quoted'          => (int) ($counts['quoted'] ?? 0),
        ];
    }
}
