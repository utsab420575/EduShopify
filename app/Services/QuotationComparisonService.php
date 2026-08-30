<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use Illuminate\Support\Collection;

/**
 * Read-only comparison-matrix builder for the Buyer's "Compare Quotations"
 * page. Never writes to rfqs/quotations/quotation_items/etc — selection
 * state lives in RFQ-scoped client localStorage; this service only ever
 * re-derives fresh data from the database for whatever quotation IDs it's
 * handed, always scoped to one already-authorized Rfq.
 *
 * Reuses the live quotation_items/quotation_item_attribute_values tables
 * directly — those ARE the current/latest-revision data (QuotationService
 * rewrites them on every revise()), so no separate revision-resolution step
 * is needed here.
 */
class QuotationComparisonService
{
    /**
     * Normalizes, dedupes, caps, and resolves the raw client-selected IDs
     * into real Quotation models — scoped through $rfq->quotations(), which
     * is what makes cross-RFQ mixing and cross-Buyer IDOR both structurally
     * impossible: an id that doesn't belong to this specific (already
     * policy-checked-as-owned) RFQ, or isn't in a buyer-visible status,
     * simply isn't in the result set.
     *
     * @return array{quotations: Collection<int, Quotation>, removed_ids: array<int>}
     */
    public function resolve(Rfq $rfq, array $quotationIds): array
    {
        $max = (int) config('quotation_comparison.max_items', 5);

        $ids = collect($quotationIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->take($max);

        if ($ids->isEmpty()) {
            return ['quotations' => collect(), 'removed_ids' => []];
        }

        $quotations = $rfq->quotations()
            ->whereIn('id', $ids)
            ->whereIn('status', config('quotation_comparison.eligible_statuses', []))
            ->with([
                'supplierAccount.supplierProfile',
                'shortlists',
                'items.attributeValues.attribute.attributeGroup',
                'items.attributeValues.attribute.unit',
                'items.attributeValues.attributeValue',
                'items.offeredListing',
                'items.offeredVariant',
                'items.unit',
            ])
            ->get()
            ->sortBy(fn (Quotation $q) => $ids->search($q->id))
            ->values();

        $removedIds = $ids->diff($quotations->pluck('id'))->values()->all();

        return ['quotations' => $quotations, 'removed_ids' => $removedIds];
    }

    /**
     * Per-quotation summary metadata (spec §15), including the RFQ-version
     * drift flag — never silently presenting a stale-version quotation as
     * equivalent to a current one.
     */
    public function buildSummary(Rfq $rfq, Collection $quotations): array
    {
        return $quotations->map(function (Quotation $q) use ($rfq) {
            return [
                'quotation_id'      => $q->id,
                'quotation_number'  => $q->quotation_number,
                'status'            => $q->status,
                'supplier_name'     => $q->supplierAccount?->supplierProfile?->display_name,
                'supplier_slug'     => $q->supplierAccount?->supplierProfile?->slug,
                'supplier_account_id' => $q->supplier_account_id,
                'rfq_version_no'    => $q->rfq_version_no,
                'rfq_version_stale' => $q->rfq_version_no !== null && $q->rfq_version_no < $rfq->current_version_no,
                'current_revision_no' => $q->current_revision_no,
                'submitted_at'      => $q->submitted_at?->format('d M Y'),
                'valid_until'       => $q->valid_until?->format('d M Y'),
                'is_expired'        => $q->hasExpired(),
                'is_shortlisted'    => $q->shortlists->isNotEmpty(),
            ];
        })->values()->all();
    }

    /**
     * Straight-from-the-row commercial fields (spec §17: never recalculate
     * the authoritative grand_total), plus objective badges that are only
     * computed when mathematically safe (same currency for the total badge —
     * spec §18/§19).
     */
    public function buildCommercial(Collection $quotations): array
    {
        $rows = $quotations->map(fn (Quotation $q) => [
            'quotation_id'    => $q->id,
            'currency_code'   => $q->currency_code,
            'subtotal'        => (float) $q->subtotal,
            'tax_amount'      => (float) $q->tax_amount,
            'discount_amount' => (float) $q->discount_amount,
            'shipping_charge' => (float) $q->shipping_charge,
            'grand_total'     => (float) $q->grand_total,
            'lead_time_days'  => $q->lead_time_days,
            'valid_until'     => $q->valid_until?->format('d M Y'),
            'payment_terms'   => $q->payment_terms,
            'warranty_terms'  => $q->warranty_terms,
            'support_terms'   => $q->support_terms,
            'proposal'        => $q->proposal,
        ])->values();

        $currencies = $rows->pluck('currency_code')->unique();
        $sameCurrency = $currencies->count() === 1;

        $badges = [
            'same_currency'          => $sameCurrency,
            'lowest_grand_total_id'  => $sameCurrency ? $rows->sortBy('grand_total')->first()['quotation_id'] ?? null : null,
            'shortest_lead_time_id'  => $rows->filter(fn ($r) => $r['lead_time_days'] !== null)->sortBy('lead_time_days')->first()['quotation_id'] ?? null,
            'longest_validity_id'    => $rows->filter(fn ($r) => $r['valid_until'] !== null)
                ->sortByDesc(fn ($r) => $quotations->firstWhere('id', $r['quotation_id'])?->valid_until)
                ->first()['quotation_id'] ?? null,
        ];

        return ['rows' => $rows->all(), 'badges' => $badges];
    }

    /**
     * One section per rfq_item (spec §20), Buyer requirement first, then
     * each quotation's matched non-addon offer(s) — a primary AND an
     * alternative both mapped to the same rfq_item_id are both rendered
     * (spec §22/T), never silently collapsed to one.
     */
    public function buildItemComparison(Rfq $rfq, Collection $quotations): array
    {
        return $rfq->items->sortBy('sort_order')->values()->map(function (RfqItem $rfqItem) use ($quotations) {
            $buyerAttrs = $rfqItem->attributeValues->keyBy('attribute_id');

            $buyerAttributeRows = $buyerAttrs->map(fn ($v) => [
                'attribute_id' => $v->attribute_id,
                'name'         => $v->attribute?->name,
                'unit'         => $v->attribute?->unit?->symbol ?? $v->attribute?->unit?->name,
                'value'        => $v->formattedValue(),
            ])->values();

            $offersByQuotation = $quotations->mapWithKeys(function (Quotation $q) use ($rfqItem, $buyerAttrs) {
                $offers = $q->items
                    ->where('rfq_item_id', $rfqItem->id)
                    ->where('is_optional_addon', false)
                    ->map(fn (QuotationItem $item) => $this->formatOffer($item, $buyerAttrs))
                    ->values();

                return [$q->id => $offers];
            });

            return [
                'rfq_item_id'      => $rfqItem->id,
                'item_name'        => $rfqItem->item_name,
                'quantity'         => rtrim(rtrim((string) $rfqItem->quantity, '0'), '.'),
                'unit'             => $rfqItem->unit?->symbol ?? $rfqItem->unit?->name ?? $rfqItem->custom_unit,
                'buyer_attributes' => $buyerAttributeRows,
                'offers'           => $offersByQuotation,
            ];
        })->values()->all();
    }

    private function formatOffer(QuotationItem $item, Collection $buyerAttrsByAttributeId): array
    {
        $offerType = $item->is_alternative
            ? 'alternative'
            : ($item->offered_listing_id ? 'existing_product' : 'custom');

        $supplierAttrs = $item->attributeValues->keyBy('attribute_id');

        $matched = $buyerAttrsByAttributeId->map(function ($buyerValue, $attributeId) use ($supplierAttrs, $item) {
            $supplierValue = $supplierAttrs->get($attributeId);

            if ($item->is_alternative) {
                $status = 'alternative';
            } elseif (! $supplierValue) {
                $status = 'missing';
            } else {
                $status = $this->valuesMatch($buyerValue, $supplierValue) ? 'match' : 'different';
            }

            return [
                'attribute_id'  => $attributeId,
                'name'          => $buyerValue->attribute?->name,
                'unit'          => $buyerValue->attribute?->unit?->symbol ?? $buyerValue->attribute?->unit?->name,
                'buyer_value'   => $buyerValue->formattedValue(),
                'supplier_value' => $supplierValue?->formattedValue(),
                'status'        => $status,
            ];
        })->values();

        $extraAttributeIds = $supplierAttrs->keys()->diff($buyerAttrsByAttributeId->keys())->all();
        $additional = $supplierAttrs->only($extraAttributeIds)->map(fn ($v) => [
            'attribute_id' => $v->attribute_id,
            'name'         => $v->attribute?->name,
            'unit'         => $v->attribute?->unit?->symbol ?? $v->attribute?->unit?->name,
            'value'        => $v->formattedValue(),
        ])->values();

        return [
            'quotation_item_id' => $item->id,
            'offer_type'        => $offerType,
            'is_alternative'    => (bool) $item->is_alternative,
            'item_name'         => $item->item_name,
            'quantity'          => rtrim(rtrim((string) $item->quantity, '0'), '.'),
            'unit'              => $item->unit?->symbol ?? $item->unit?->name ?? $item->custom_unit,
            'unit_price'        => (float) $item->unit_price,
            'tax_amount'        => (float) $item->tax_amount,
            'discount_amount'   => (float) $item->discount_amount,
            'line_total'        => (float) $item->line_total,
            'lead_time_days'    => $item->lead_time_days,
            'offered_listing'   => $item->offeredListing ? ['id' => $item->offeredListing->id, 'name' => $item->offeredListing->name, 'slug' => $item->offeredListing->slug] : null,
            'offered_variant'   => $item->offeredVariant ? ['id' => $item->offeredVariant->id, 'name' => $item->offeredVariant->name] : null,
            'attributes'        => $matched->all(),
            'additional_specifications' => $additional->all(),
        ];
    }

    /**
     * Normalized value comparison — trims and case-folds strings so
     * formatting noise ("16GB" vs "16 GB") doesn't matter for booleans/
     * numbers already rendered consistently by formattedValue(), while
     * still comparing the real resolved value rather than raw DB columns.
     */
    private function valuesMatch($buyerAttributeValue, $supplierAttributeValue): bool
    {
        $a = mb_strtolower(trim((string) $buyerAttributeValue->formattedValue()));
        $b = mb_strtolower(trim((string) $supplierAttributeValue->formattedValue()));

        return $a !== '' && $a === $b;
    }

    /**
     * Optional add-ons (spec §41) — kept entirely separate from RFQ item
     * coverage. quotation_items.is_optional_addon is reused as-is; no new
     * column, no new mechanism.
     */
    public function buildAddons(Collection $quotations): array
    {
        return $quotations->mapWithKeys(function (Quotation $q) {
            $addons = $q->items->where('is_optional_addon', true)->values();

            $rows = $addons->map(fn (QuotationItem $item) => [
                'quotation_item_id' => $item->id,
                'item_name'         => $item->item_name,
                'quantity'          => rtrim(rtrim((string) $item->quantity, '0'), '.'),
                'unit'              => $item->unit?->symbol ?? $item->unit?->name ?? $item->custom_unit,
                'unit_price'        => (float) $item->unit_price,
                'line_total'        => (float) $item->line_total,
                // Flagged per spec §42/AE — a row that is somehow both an
                // addon and an alternative violates the documented business
                // rule and must never be silently rendered as one or the other.
                'data_violation'    => (bool) $item->is_alternative,
            ]);

            return [$q->id => [
                'items' => $rows->all(),
                'addon_line_total' => (float) $rows->sum('line_total'),
            ]];
        })->all();
    }

    /**
     * "X of Y RFQ items quoted" — counts distinct non-addon rfq_item_ids
     * only. Add-ons must never inflate this count (spec Test Group AF).
     */
    public function buildPartialSummary(Rfq $rfq, Collection $quotations): array
    {
        $totalItems = $rfq->items->count();

        return $quotations->mapWithKeys(function (Quotation $q) use ($totalItems) {
            $quoted = $q->items
                ->where('is_optional_addon', false)
                ->pluck('rfq_item_id')
                ->filter()
                ->unique()
                ->count();

            return [$q->id => [
                'quoted_count' => $quoted,
                'total_count'  => $totalItems,
                'is_full'      => $totalItems > 0 && $quoted >= $totalItems,
            ]];
        })->all();
    }
}
