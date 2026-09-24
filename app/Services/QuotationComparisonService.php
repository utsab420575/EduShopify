<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationItemOffer;
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
                'items.media',
                'items.offers.marketplaceProduct.media',
                'items.offers.offeredVariant',
                'items.offers.unit',
                'items.offers.media',
                'items.offers.attributeValues.attribute.attributeGroup',
                'items.offers.attributeValues.attribute.unit',
                'items.offers.attributeValues.attributeValue',
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
        // Computed once for the whole RFQ (not per quotation) — mirrors
        // QuotationPolicy::award()'s "only one non-rejected/non-cancelled
        // award may be active per RFQ" exclusivity check.
        $hasPendingAward = $rfq->awards()->where('status', 'pending_supplier_response')->exists();

        return $quotations->map(function (Quotation $q) use ($rfq, $hasPendingAward) {
            $profile = $q->supplierAccount?->supplierProfile;
            $logoUrl = null;
            if ($profile && ! empty($profile->logo)) {
                $logoUrl = str_starts_with($profile->logo, 'http') ? $profile->logo : asset('storage/' . $profile->logo);
            }

            return [
                'quotation_id'      => $q->id,
                'quotation_number'  => $q->quotation_number,
                'status'            => $q->status,
                'supplier_name'     => $profile?->display_name ?? 'Supplier',
                'supplier_slug'     => $profile?->slug,
                'supplier_logo'     => $logoUrl,
                'supplier_account_id' => $q->supplier_account_id,
                'rfq_version_no'    => $q->rfq_version_no,
                'rfq_version_stale' => $q->rfq_version_no !== null && $q->rfq_version_no < $rfq->current_version_no,
                'current_revision_no' => $q->current_revision_no,
                'submitted_at'      => $q->submitted_at?->format('d M Y'),
                'valid_until'       => $q->valid_until?->format('d M Y'),
                'is_expired'        => $q->hasExpired(),
                'is_shortlisted'    => $q->shortlists->isNotEmpty(),
                'supplier_rating'   => $profile?->rating !== null
                    ? (float) $profile->rating : null,
                'supplier_reviews_count' => $profile?->reviews_count,
                // Every quotation reaching this point is already scoped
                // through $rfq->quotations() (resolve()), so ownership is
                // guaranteed — only the status gate from
                // QuotationPolicy::selectOffer()/::award() needs repeating
                // here (award() also single-flights on "no pending award
                // already in flight for the RFQ", checked once below).
                'can_select_offer'  => in_array($q->status, ['submitted', 'revised', 'shortlisted', 'under_review'], true),
                'can_award'         => in_array($q->status, ['submitted', 'revised', 'shortlisted', 'under_review'], true)
                    && ! $hasPendingAward,
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
        $rows = $quotations->map(function (Quotation $q) {
            $effectiveSubtotal = 0.0;
            $effectiveTax = 0.0;
            $effectiveDiscount = 0.0;
            $hasAlternativeSelected = false;

            foreach ($q->items as $item) {
                if (! $item->rfq_item_id) {
                    continue;
                }
                $activeOffer = $item->offers->firstWhere('is_selected', true)
                    ?? $item->offers->firstWhere('is_primary', true)
                    ?? $item->offers->first();

                if ($activeOffer) {
                    if ($activeOffer->is_selected && ! $activeOffer->is_primary) {
                        $hasAlternativeSelected = true;
                    }
                    $effectiveSubtotal += round((float) $activeOffer->quantity * (float) $activeOffer->unit_price, 2);
                    $effectiveTax += (float) ($activeOffer->tax_amount ?? 0);
                    $effectiveDiscount += (float) ($activeOffer->discount ?? 0);
                } else {
                    $effectiveSubtotal += round((float) $item->quantity * (float) $item->unit_price, 2);
                    $effectiveTax += (float) ($item->tax_amount ?? 0);
                    $effectiveDiscount += (float) ($item->discount_amount ?? 0);
                }
            }

            $shipping = (float) $q->shipping_charge;
            $effectiveGrandTotal = round($effectiveSubtotal - $effectiveDiscount + $effectiveTax + $shipping, 2);

            return [
                'quotation_id'            => $q->id,
                'currency_code'           => $q->currency_code,
                'subtotal'                => (float) $q->subtotal,
                'tax_amount'              => (float) $q->tax_amount,
                'discount_amount'         => (float) $q->discount_amount,
                'shipping_charge'         => $shipping,
                'grand_total'             => (float) $q->grand_total,
                'effective_grand_total'   => $effectiveGrandTotal,
                'has_alternative_selected'=> $hasAlternativeSelected,
                'lead_time_days'          => $q->lead_time_days,
                'valid_until'             => $q->valid_until?->format('d M Y'),
                'payment_terms'           => $q->payment_terms,
                'warranty_terms'          => $q->warranty_terms,
                'support_terms'           => $q->support_terms,
                'proposal'                => $q->proposal,
            ];
        })->values();

        $currencies = $rows->pluck('currency_code')->unique();
        $sameCurrency = $currencies->count() === 1;

        $badges = [
            'same_currency'          => $sameCurrency,
            'lowest_grand_total_id'  => $sameCurrency ? $rows->sortBy('effective_grand_total')->first()['quotation_id'] ?? null : null,
            'shortest_lead_time_id'  => $rows->filter(fn ($r) => $r['lead_time_days'] !== null)->sortBy('lead_time_days')->first()['quotation_id'] ?? null,
            'longest_validity_id'    => $rows->filter(fn ($r) => $r['valid_until'] !== null)
                ->sortByDesc(fn ($r) => $quotations->firstWhere('id', $r['quotation_id'])?->valid_until)
                ->first()['quotation_id'] ?? null,
        ];

        return ['rows' => $rows->all(), 'badges' => $badges];
    }

    /**
     * One section per rfq_item (spec §20), Buyer requirement first, then
     * each quotation's Product Response(s) for that item, each with every
     * one of its Offers rendered (never collapsed to just the primary) —
     * spec §22/T's "never silently collapse an alternative" intent, now
     * carried entirely by quotation_item_offers rather than
     * quotation_items.is_alternative (unused).
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

            // rfq_item_id is never null for a real RFQ-item response, and
            // add-on quotation_items are always created with rfq_item_id
            // null (QuotationService::syncItems()) — this where() alone
            // already excludes them, no need to also check
            // is_optional_addon (unused per the finalized decision).
            $offersByQuotation = $quotations->mapWithKeys(function (Quotation $q) use ($rfqItem, $buyerAttrs) {
                $productResponses = $q->items
                    ->where('rfq_item_id', $rfqItem->id)
                    ->map(fn (QuotationItem $item) => $this->formatProductResponse($item, $buyerAttrs))
                    ->values();

                return [$q->id => $productResponses];
            });

            return [
                'rfq_item_id'          => $rfqItem->id,
                'item_name'            => $rfqItem->item_name,
                'category_name'        => $rfqItem->category?->name,
                'quantity'             => rtrim(rtrim((string) $rfqItem->quantity, '0'), '.'),
                'unit'                 => $rfqItem->unit?->symbol ?? $rfqItem->unit?->name ?? $rfqItem->custom_unit,
                'estimated_unit_price' => $rfqItem->estimated_unit_price ? (float) $rfqItem->estimated_unit_price : null,
                'description'          => $rfqItem->description,
                // Which of the three RFQ-item creation modes the buyer used —
                // drives the per-item type badge on the comparison page.
                'item_type'            => $rfqItem->isMarketplaceProduct()
                    ? 'marketplace_product'
                    : ($rfqItem->isRequirement() ? 'quotation_only' : 'custom_product'),
                'buyer_attributes'     => $buyerAttributeRows,
                'buyer_attachments'    => method_exists($rfqItem, 'getMedia') ? $rfqItem->getMedia('attachments')->map(fn ($m) => [
                    'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                    'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
                ])->values()->all() : [],
                'offers'               => $offersByQuotation,
            ];
        })->values()->all();
    }

    /**
     * One Product Response (quotation_items row) — the RFQ-item-level
     * structured attribute comparison still lives here, sourced from the
     * ACTIVE offer's attribute values (selected if buyer chose one, else primary),
     * plus the nested list of every individual Offer (quotation_item_offers).
     */
    private function formatProductResponse(QuotationItem $item, Collection $buyerAttrsByAttributeId): array
    {
        // Find the active offer: explicit buyer selection first, else primary, else first offer
        $activeOffer = $item->offers->firstWhere('is_selected', true)
            ?? $item->offers->firstWhere('is_primary', true)
            ?? $item->offers->first();
        $activeOfferId = $activeOffer?->id;

        $supplierAttrs = $item->attributeValues->where('quotation_item_offer_id', $activeOfferId)->keyBy('attribute_id');
        if ($supplierAttrs->isEmpty()) {
            $supplierAttrs = $item->attributeValues->whereNull('quotation_item_offer_id')->keyBy('attribute_id');
        }

        $matched = $buyerAttrsByAttributeId->map(function ($buyerValue, $attributeId) use ($supplierAttrs) {
            $supplierValue = $supplierAttrs->get($attributeId);

            if (! $supplierValue) {
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
        $additional = $supplierAttrs->filter(fn ($v, $attributeId) => in_array($attributeId, $extraAttributeIds))->map(fn ($v) => [
            'attribute_id' => $v->attribute_id,
            'name'         => $v->attribute?->name,
            'unit'         => $v->attribute?->unit?->symbol ?? $v->attribute?->unit?->name,
            'value'        => $v->formattedValue(),
        ])->values();

        return [
            'quotation_item_id' => $item->id,
            'item_name'         => $item->item_name,
            'quantity'          => rtrim(rtrim((string) $item->quantity, '0'), '.'),
            'unit'              => $item->unit?->symbol ?? $item->unit?->name ?? $item->custom_unit,
            'unit_price'        => (float) $item->unit_price,
            'tax_amount'        => (float) $item->tax_amount,
            'discount_amount'   => (float) $item->discount_amount,
            'line_total'        => (float) $item->line_total,
            'lead_time_days'    => $item->lead_time_days,
            'active_offer_id'   => $activeOfferId,
            'offered_listing'   => $item->offeredListing ? ['id' => $item->offeredListing->id, 'name' => $item->offeredListing->name, 'slug' => $item->offeredListing->slug] : null,
            'offered_variant'   => $item->offeredVariant ? ['id' => $item->offeredVariant->id, 'name' => $item->offeredVariant->name] : null,
            'documents'         => $item->getMedia('document')->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
            ])->values()->all(),
            'attributes'        => $matched->all(),
            'additional_specifications' => $additional->all(),
            'offers'            => $item->offers->map(fn (QuotationItemOffer $offer) => $this->formatSingleOffer($offer, $buyerAttrsByAttributeId, $activeOfferId))->values()->all(),
        ];
    }

    /**
     * One individual Offer (quotation_item_offers row) under a Product
     * Response — its own method/price/quantity/delivery time/documents,
     * its free-text "Additional Specifications", its own structured category attribute values,
     * and its matched attribute statuses against buyer requirements.
     */
    private function formatSingleOffer(QuotationItemOffer $offer, ?Collection $buyerAttrsByAttributeId = null, ?int $activeOfferId = null): array
    {
        $offerAttrs = $offer->attributeValues->keyBy('attribute_id');
        $matched = collect();
        $additional = collect();

        if ($buyerAttrsByAttributeId && $buyerAttrsByAttributeId->isNotEmpty()) {
            $matched = $buyerAttrsByAttributeId->map(function ($buyerValue, $attributeId) use ($offerAttrs) {
                $offerValue = $offerAttrs->get($attributeId);

                if (! $offerValue) {
                    $status = 'missing';
                } else {
                    $status = $this->valuesMatch($buyerValue, $offerValue) ? 'match' : 'different';
                }

                return [
                    'attribute_id'   => $attributeId,
                    'name'           => $buyerValue->attribute?->name,
                    'unit'           => $buyerValue->attribute?->unit?->symbol ?? $buyerValue->attribute?->unit?->name,
                    'buyer_value'    => $buyerValue->formattedValue(),
                    'supplier_value' => $offerValue?->formattedValue(),
                    'status'         => $status,
                ];
            })->values();

            $extraAttributeIds = $offerAttrs->keys()->diff($buyerAttrsByAttributeId->keys())->all();
            $additional = $offerAttrs->filter(fn ($v, $attributeId) => in_array($attributeId, $extraAttributeIds))->map(fn ($v) => [
                'attribute_id' => $v->attribute_id,
                'name'         => $v->attribute?->name,
                'unit'         => $v->attribute?->unit?->symbol ?? $v->attribute?->unit?->name,
                'value'        => $v->formattedValue(),
            ])->values();
        }

        $isActive = $activeOfferId !== null
            ? $offer->id === $activeOfferId
            : (bool) ($offer->is_selected || ($offer->is_primary && ! $offer->quotationItem?->offers->contains('is_selected', true)));

        $thumbUrl = null;
        if ($offer->marketplaceProduct) {
            $mp = $offer->marketplaceProduct;
            $thumbUrl = $mp->relationLoaded('media')
                ? ($mp->media->where('collection_name', 'gallery')->first()?->getUrl() ?: null)
                : ($mp->getFirstMediaUrl('gallery') ?: null);
        }

        return [
            'id'               => $offer->id,
            'offer_method'     => $offer->offer_method,
            'product_name'     => $offer->product_name,
            'description'      => $offer->description,
            'quantity'         => rtrim(rtrim((string) $offer->quantity, '0'), '.'),
            'unit'             => $offer->unit?->symbol ?? $offer->unit?->name ?? $offer->custom_unit,
            'unit_price'       => (float) $offer->unit_price,
            'tax_amount'       => (float) $offer->tax_amount,
            'discount_amount'  => (float) $offer->discount,
            'total_price'      => (float) $offer->total_price,
            'delivery_time'    => $offer->delivery_time,
            'is_primary'       => (bool) $offer->is_primary,
            'is_selected'      => (bool) $offer->is_selected,
            'is_active'        => $isActive,
            'offered_listing'  => $offer->marketplaceProduct ? [
                'id'        => $offer->marketplaceProduct->id,
                'name'      => $offer->marketplaceProduct->name,
                'slug'      => $offer->marketplaceProduct->slug,
                'thumb_url' => $thumbUrl,
            ] : null,
            'offered_variant'  => $offer->offeredVariant ? ['id' => $offer->offeredVariant->id, 'name' => $offer->offeredVariant->name] : null,
            'documents'        => $offer->getMedia('document')->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
            ])->values()->all(),
            'specifications'   => is_array($offer->specifications) ? $offer->specifications : [],
            'attributes'       => $offer->attributeValues->map(fn ($v) => [
                'attribute_id' => $v->attribute_id,
                'name'         => $v->attribute?->name,
                'unit'         => $v->attribute?->unit?->symbol ?? $v->attribute?->unit?->name,
                'value'        => $v->formattedValue(),
            ])->values()->all(),
            'matched_attributes' => $matched->all(),
            'additional_attributes' => $additional->all(),
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
            // ->filter() alone already drops add-ons (rfq_item_id is always
            // null for them) — is_optional_addon is unused per the
            // finalized decision.
            $quoted = $q->items
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
