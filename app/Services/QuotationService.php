<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationItemAttributeValue;
use App\Models\QuotationItemOffer;
use App\Models\QuotationRevision;
use App\Models\QuotationRevisionItem;
use App\Models\QuotationRevisionItemAttributeValue;
use App\Models\QuotationRevisionItemOffer;
use App\Models\QuotationRevisionRequest;
use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\User;
use App\Notifications\DashboardNotification;
use App\Services\QuotationActivityService;
use App\Services\SupplierRfqActionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Supplier-side quotation authoring.
 *
 * A quotation begins as a real, incomplete-tolerant draft (saveDraft() — no
 * revision exists yet, current_revision_no stays 0) and is promoted to the
 * live commercial response by submitDraft(), which is the first point a
 * revision snapshot is written. From there, further changes only happen
 * through revise() (reachable only for submitted/revision_requested
 * quotations, per QuotationPolicy), which bumps the revision number and
 * writes another immutable snapshot — never overwriting prior revision
 * history. Every snapshot also freezes the item's structured attribute
 * values (quotation_revision_item_attribute_values) so an earlier revision's
 * specs survive a later revision unchanged (spec §31).
 */
class QuotationService
{
    /**
     * The client_ref => persisted id map from the most recent saveDraft()
     * call on this instance — see getClientRefItemIds(). Deliberately kept
     * as service-instance state, not attached to the Quotation model: an
     * earlier attempt used Eloquent's setAttribute() to stash it on the
     * returned model, but that pollutes the model's dirty-attribute
     * tracking, so a later ->update()/->save() on that same instance (e.g.
     * submitDraft() called right after saveDraft() in revise()/submit()
     * flows) tried to persist a nonexistent "client_ref_item_ids" column.
     */
    private array $lastClientRefItemIds = [];

    public function __construct(
        private SupplierRfqActionService $supplierRfqActions,
        private QuotationActivityService $quotationActivities,
    ) {
    }

    public function saveDraft(Rfq $rfq, Account $supplierAccount, User $user, array $data, ?Quotation $quotation = null): Quotation
    {
        return DB::transaction(function () use ($rfq, $supplierAccount, $user, $data, $quotation) {
            $totals = $this->computeTotals($data['items'] ?? []);
            $shipping = round((float) ($data['shipping_charge'] ?? 0), 2);
            $grandTotal = round($totals['subtotal'] - $totals['discount_amount'] + $totals['tax_amount'] + $shipping, 2);

            $attributes = [
                'currency_code'    => $this->resolveCurrency($data['currency_code'] ?? $quotation?->currency_code ?? $rfq->currency_code),
                'current_step'     => isset($data['current_step']) ? (int) $data['current_step'] : ($quotation?->current_step ?? 1),
                // Forward-only — never let an autosave regress this even if
                // the client sends a lower value (e.g. a stale tab reloaded
                // after the supplier progressed further in another tab).
                'max_completed_step' => max(
                    isset($data['max_completed_step']) ? (int) $data['max_completed_step'] : 1,
                    $quotation?->max_completed_step ?? 1
                ),
                'subtotal'         => $totals['subtotal'],
                'tax_amount'       => $totals['tax_amount'],
                'discount_amount'  => $totals['discount_amount'],
                'shipping_charge'  => $shipping,
                'grand_total'      => $grandTotal,
                'lead_time_days'   => $data['lead_time_days'] ?? null,
                'valid_until'      => $data['valid_until'] ?? null,
                'warranty_terms'   => $data['warranty_terms'] ?? null,
                'support_terms'    => $data['support_terms'] ?? null,
                'payment_terms'    => $data['payment_terms'] ?? null,
                'proposal'         => $data['proposal'] ?? null,
            ];

            if ($quotation) {
                $quotation->update($attributes);
            } else {
                $quotation = Quotation::create($attributes + [
                    'quotation_number'     => $this->generateQuotationNumber(),
                    'rfq_id'               => $rfq->id,
                    'supplier_account_id'  => $supplierAccount->id,
                    'submitted_by_user_id' => $user->id,
                    'rfq_version_no'       => $rfq->current_version_no,
                    'current_revision_no'  => 0,
                    'status'               => 'draft',
                ]);
            }

            $this->lastClientRefItemIds = $this->syncItems($quotation, $data['items'] ?? []);

            return $quotation->fresh(['items.attributeValues']);
        });
    }

    /**
     * Reads back the client_ref => persisted id map from the most recent
     * saveDraft() call on this same service instance, for the autosave
     * endpoints to echo to the client — safe because a controller resolves
     * one QuotationService instance per request and calls saveDraft() then
     * this immediately after, never interleaved with another save.
     */
    public function getLastClientRefItemIds(): array
    {
        return $this->lastClientRefItemIds;
    }

    /**
     * Promotes a draft to the live commercial response. Re-validates
     * completeness (partial-quotation/alternative-product rules, offered
     * listings actually belonging to this supplier) and guards against
     * silently submitting against a stale RFQ version (spec §27) — pass
     * $acknowledgeVersionChange=true only after the supplier has explicitly
     * reviewed the diff and chosen to submit anyway.
     */
    public function submitDraft(Quotation $quotation, bool $acknowledgeVersionChange = false): Quotation
    {
        $quotation->loadMissing(['rfq.items', 'items']);
        $rfq = $quotation->rfq;

        $this->assertItems($rfq, $quotation->supplier_account_id, $quotation->items);

        if ($quotation->rfq_version_no !== $rfq->current_version_no && ! $acknowledgeVersionChange) {
            throw ValidationException::withMessages([
                'rfq_version' => "The RFQ has changed from version {$quotation->rfq_version_no} to version {$rfq->current_version_no} since you started this quotation. Review the changes before submitting.",
            ]);
        }

        return DB::transaction(function () use ($quotation, $rfq) {
            $quotation->update([
                'rfq_version_no'      => $rfq->current_version_no,
                'current_revision_no' => 1,
                'status'              => 'submitted',
                'submitted_at'        => now(),
            ]);

            $rfq->increment('quotations_count');

            $quotation = $quotation->fresh(['items.attributeValues', 'items.offers']);
            $this->snapshotRevision($quotation, $quotation->submittedBy);

            RfqSupplierQueue::where('rfq_id', $rfq->id)
                ->where('supplier_account_id', $quotation->supplier_account_id)
                ->update(['status' => 'quotation_submitted']);

            $this->supplierRfqActions->record($rfq, $quotation->supplierAccount, 'quoted');
            $this->quotationActivities->record($quotation, 'submitted');

            $this->notifyBuyer($rfq, "New quotation received for \"{$rfq->title}\".", $this->buyerRfqUrl($rfq));

            return $quotation;
        });
    }

    /**
     * Back-compat wrapper for older call sites still using the one-shot
     * submit signature — internally just chains the real two-step flow.
     * $acknowledgeVersionChange defaults to false, which is correct here:
     * the draft is created and submitted in the same call, so there's no
     * window for the RFQ to have changed version in between.
     */
    public function submit(Rfq $rfq, Account $supplierAccount, User $user, array $data, ?Quotation $quotation = null): Quotation
    {
        $draft = $this->saveDraft($rfq, $supplierAccount, $user, $data, $quotation);

        return $this->submitDraft($draft);
    }

    public function revise(Quotation $quotation, array $data, ?User $user = null): Quotation
    {
        $this->assertItems($quotation->rfq, $quotation->supplier_account_id, $data['items'] ?? []);

        return DB::transaction(function () use ($quotation, $data, $user) {
            $totals = $this->computeTotals($data['items']);
            $shipping = round((float) ($data['shipping_charge'] ?? $quotation->shipping_charge ?? 0), 2);
            $grandTotal = round($totals['subtotal'] - $totals['discount_amount'] + $totals['tax_amount'] + $shipping, 2);

            $quotation->update([
                'current_revision_no' => $quotation->current_revision_no + 1,
                'subtotal'            => $totals['subtotal'],
                'tax_amount'          => $totals['tax_amount'],
                'discount_amount'     => $totals['discount_amount'],
                'shipping_charge'     => $shipping,
                'grand_total'         => $grandTotal,
                'lead_time_days'      => $data['lead_time_days'] ?? $quotation->lead_time_days,
                'valid_until'         => $data['valid_until'] ?? $quotation->valid_until,
                'warranty_terms'      => $data['warranty_terms'] ?? $quotation->warranty_terms,
                'support_terms'       => $data['support_terms'] ?? $quotation->support_terms,
                'payment_terms'       => $data['payment_terms'] ?? $quotation->payment_terms,
                'proposal'            => $data['proposal'] ?? $quotation->proposal,
                'rfq_version_no'      => $quotation->rfq->current_version_no,
                'status'              => 'revised',
                'revised_at'          => now(),
            ]);

            $this->syncItems($quotation, $data['items']);

            QuotationRevisionRequest::where('quotation_id', $quotation->id)
                ->where('status', 'pending')
                ->update(['status' => 'revised', 'responded_at' => now()]);

            $quotation = $quotation->fresh(['items.attributeValues', 'items.offers']);
            $this->snapshotRevision($quotation, $user ?? $quotation->submittedBy, $data['change_summary'] ?? null);

            $this->quotationActivities->record($quotation, 'quotation_updated', $data['change_summary'] ?? null);

            $this->notifyBuyer($quotation->rfq, "The quotation for \"{$quotation->rfq->title}\" was revised by the supplier.", $this->buyerRfqUrl($quotation->rfq));

            return $quotation;
        });
    }

    /**
     * Immutable copy of the quotation's CURRENT state (just after it was set)
     * into quotation_revisions/quotation_revision_items(+attribute values),
     * keyed by the revision number that is now live. Never overwritten.
     */
    private function snapshotRevision(Quotation $quotation, ?User $user, ?string $changeSummary = null): void
    {
        $revision = QuotationRevision::create([
            'quotation_id' => $quotation->id,
            'revision_no' => $quotation->current_revision_no,
            'rfq_version_no' => $quotation->rfq_version_no,
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'discount_amount' => $quotation->discount_amount,
            'shipping_charge' => $quotation->shipping_charge,
            'grand_total' => $quotation->grand_total,
            'currency_code' => $quotation->currency_code,
            'lead_time_days' => $quotation->lead_time_days,
            'valid_until' => $quotation->valid_until,
            'warranty_terms' => $quotation->warranty_terms,
            'support_terms' => $quotation->support_terms,
            'payment_terms' => $quotation->payment_terms,
            'proposal' => $quotation->proposal,
            'change_summary' => $changeSummary,
            'created_by_account_id' => $quotation->supplier_account_id,
            'created_by_user_id' => $user?->id,
        ]);

        foreach ($quotation->items as $item) {
            $revisionItem = QuotationRevisionItem::create([
                'quotation_revision_id' => $revision->id,
                'rfq_item_id' => $item->rfq_item_id,
                'offered_listing_id' => $item->offered_listing_id,
                'offered_variant_id' => $item->offered_variant_id,
                'is_alternative' => $item->is_alternative,
                'is_optional_addon' => $item->is_optional_addon,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_id' => $item->unit_id,
                'custom_unit' => $item->custom_unit,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'discount_amount' => $item->discount_amount,
                'line_total' => $item->line_total,
                'lead_time_days' => $item->lead_time_days,
                'specs' => $item->specs,
            ]);

            foreach ($item->attributeValues as $value) {
                QuotationRevisionItemAttributeValue::create([
                    'quotation_revision_item_id' => $revisionItem->id,
                    'attribute_id' => $value->attribute_id,
                    'attribute_value_id' => $value->attribute_value_id,
                    'custom_value' => $value->custom_value,
                    'value_text' => $value->value_text,
                    'value_number' => $value->value_number,
                    'value_boolean' => $value->value_boolean,
                    'value_date' => $value->value_date,
                    'value_json' => $value->value_json,
                ]);
            }

            // Every offer (primary AND alternatives), not just the one that
            // currently counts toward totals — a revision represents the
            // complete quotation state at that point in time.
            foreach ($item->offers as $offer) {
                $revisionOffer = QuotationRevisionItemOffer::create([
                    'quotation_revision_item_id' => $revisionItem->id,
                    'offer_method' => $offer->offer_method,
                    'marketplace_product_id' => $offer->marketplace_product_id,
                    'offered_variant_id' => $offer->offered_variant_id,
                    'product_name' => $offer->product_name,
                    'category_id' => $offer->category_id,
                    'description' => $offer->description,
                    'specifications' => $offer->specifications,
                    'quantity' => $offer->quantity,
                    'unit_id' => $offer->unit_id,
                    'custom_unit' => $offer->custom_unit,
                    'unit_price' => $offer->unit_price,
                    'tax_rate' => $offer->tax_rate,
                    'tax_amount' => $offer->tax_amount,
                    'discount' => $offer->discount,
                    'total_price' => $offer->total_price,
                    'delivery_time' => $offer->delivery_time,
                    'status' => $offer->status,
                    'is_primary' => $offer->is_primary,
                    'is_selected' => $offer->is_selected,
                    'sort_order' => $offer->sort_order,
                    'source_offer_id' => $offer->id,
                ]);

                // Physically duplicate any attached document(s) so this
                // revision stays intact even if the live offer is later
                // edited or removed by a subsequent revision.
                foreach (['document', 'gallery'] as $collection) {
                    foreach ($offer->getMedia($collection) as $media) {
                        $media->copy($revisionOffer, $collection);
                    }
                }
            }
        }
    }

    public function withdraw(Quotation $quotation, ?string $reason = null): Quotation
    {
        $quotation->update([
            'status'       => 'withdrawn',
            'withdrawn_at' => now(),
            'proposal'     => $reason ? trim(($quotation->proposal ?? '') . "\n\n[Withdrawn: {$reason}]") : $quotation->proposal,
        ]);

        $this->notifyBuyer($quotation->rfq, "A supplier withdrew their quotation for \"{$quotation->rfq->title}\".", $this->buyerRfqUrl($quotation->rfq));

        return $quotation;
    }

    /**
     * "Clone from a previous quotation" — a literal structural copy (like
     * RfqController::duplicate()) doesn't fit here, because $source's items
     * belong to a *different* RFQ than $targetRfq, so their rfq_item_id
     * can't just be reused. Instead this greedily best-matches each of
     * $targetRfq's items against $source's items (category match + name
     * similarity) and returns only the pricing/terms worth carrying over —
     * item_name, rfq_item_id, category_id and quantity always stay tied to
     * $targetRfq's own item, never copied from the source.
     *
     * @return array<int, array{unit_price: ?float, tax_rate: ?float, discount_amount: ?float, lead_time_days: ?int, description: ?string, attribute_values: array}>
     *         keyed by $targetRfq item id.
     */
    public function matchQuotationToRfq(Quotation $source, Rfq $targetRfq): array
    {
        $source->loadMissing(['items.rfqItem', 'items.attributeValues']);
        $targetRfq->loadMissing('items');

        $sourceItems = $source->items->where('is_optional_addon', false)->values();

        $pairs = [];
        foreach ($targetRfq->items as $targetItem) {
            foreach ($sourceItems as $sourceItem) {
                $score = 0.0;
                $sourceCategoryId = $sourceItem->rfqItem?->category_id;
                if ($targetItem->category_id && $sourceCategoryId === $targetItem->category_id) {
                    $score += 0.6;
                }
                similar_text(strtolower($targetItem->item_name ?? ''), strtolower($sourceItem->item_name ?? ''), $pct);
                $score += 0.4 * ($pct / 100);

                if ($score >= 0.3) {
                    $pairs[] = ['target' => $targetItem, 'source' => $sourceItem, 'score' => $score];
                }
            }
        }

        usort($pairs, fn ($a, $b) => $b['score'] <=> $a['score']);

        $matches = [];
        $usedTargetIds = [];
        $usedSourceIds = [];

        foreach ($pairs as $pair) {
            $targetItem = $pair['target'];
            $sourceItem = $pair['source'];
            if (isset($usedTargetIds[$targetItem->id]) || isset($usedSourceIds[$sourceItem->id])) {
                continue;
            }

            $usedTargetIds[$targetItem->id] = true;
            $usedSourceIds[$sourceItem->id] = true;

            $sameCategory = $targetItem->category_id && $sourceItem->rfqItem?->category_id === $targetItem->category_id;

            $matches[$targetItem->id] = [
                'unit_price'       => $sourceItem->unit_price,
                'tax_rate'         => $sourceItem->tax_rate,
                'discount_amount'  => $sourceItem->discount_amount,
                'lead_time_days'   => $sourceItem->lead_time_days,
                'description'      => $sourceItem->description,
                'attribute_values' => $sameCategory
                    ? $sourceItem->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => [
                        'attribute_value_id' => $v->attribute_value_id,
                        'custom_value'       => $v->custom_value,
                        'value_text'         => $v->value_text,
                        'value_number'       => $v->value_number,
                        'value_boolean'      => $v->value_boolean,
                        'value_date'         => $v->value_date,
                        'value_json'         => $v->value_json,
                    ]])->all()
                    : [],
            ];
        }

        return $matches;
    }

    /**
     * Works uniformly against either raw request-array items (saveDraft/
     * revise, before they're persisted) or a persisted QuotationItem
     * collection (submitDraft, after saveDraft already wrote them) — the
     * $field closure normalizes access either way.
     */
    private function assertItems(Rfq $rfq, int $supplierAccountId, iterable $items): void
    {
        $items = collect($items);
        $field = fn ($item, string $key) => is_array($item) ? ($item[$key] ?? null) : $item->{$key};

        $requestedItems = $items->reject(fn ($i) => (bool) $field($i, 'is_optional_addon'));

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Quote at least one RFQ item.']);
        }

        if (! $rfq->allow_partial_quotation) {
            $rfqItemIds = $rfq->items->pluck('id')->all();
            $quotedRfqItemIds = $requestedItems->map(fn ($i) => $field($i, 'rfq_item_id'))->filter()->all();

            if (count(array_diff($rfqItemIds, $quotedRfqItemIds)) > 0) {
                throw ValidationException::withMessages(['items' => 'This RFQ does not allow partial quotations — quote every item.']);
            }
        }

        if (! $rfq->allow_alternative_products && $items->contains(fn ($i) => (bool) $field($i, 'is_alternative'))) {
            throw ValidationException::withMessages(['items' => 'This RFQ does not allow alternative products.']);
        }

        $listingIds = $items->map(fn ($i) => $field($i, 'offered_listing_id'))->filter()->unique();
        if ($listingIds->isNotEmpty()) {
            $ownedCount = Listing::where('supplier_account_id', $supplierAccountId)->whereIn('id', $listingIds)->count();
            if ($ownedCount !== $listingIds->count()) {
                throw ValidationException::withMessages(['items' => 'One of the offered listings does not belong to your account.']);
            }
        }
    }

    /**
     * A quotation always carries a currency — purchase_orders.currency_code
     * (created from it on award acceptance) is NOT NULL, so a blank RFQ
     * currency must not propagate all the way to a constraint violation there.
     */
    private function resolveCurrency(?string $currency): string
    {
        return $currency ?? Currency::where('is_default', true)->value('code') ?? 'USD';
    }

    /**
     * Server-side, per-item: line subtotal = qty*unit_price; tax is either
     * rate-derived (tax_rate% of subtotal-after-discount) or a directly
     * entered tax_amount; discount is entered directly. Quotation-level
     * subtotal/tax/discount are sums across all items (add-ons included —
     * they contribute to totals like any other line); shipping is a
     * separate header-level figure the caller folds in. Never trusts
     * client-supplied totals (spec §23).
     */
    private function computeTotals(array $items): array
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $discountAmount = 0.0;

        foreach ($items as $item) {
            if (! empty($item['offers']) && is_array($item['offers'])) {
                $primaryOffer = collect($item['offers'])->firstWhere('is_primary', true) ?? ($item['offers'][0] ?? null);
                if ($primaryOffer) {
                    $item['quantity'] = $primaryOffer['quantity'] ?? ($item['quantity'] ?? 1);
                    $item['unit_price'] = $primaryOffer['unit_price'] ?? ($item['unit_price'] ?? 0);
                    $item['discount_amount'] = isset($primaryOffer['discount']) ? (float) $primaryOffer['discount'] : ($item['discount_amount'] ?? 0);
                    $item['tax_rate'] = isset($primaryOffer['tax_rate']) && $primaryOffer['tax_rate'] !== '' && $primaryOffer['tax_rate'] !== null ? (float) $primaryOffer['tax_rate'] : ($item['tax_rate'] ?? null);
                }
            }

            [$lineSubtotal, $lineDiscount, $lineTax] = $this->computeLine($item);
            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $discountAmount += $lineDiscount;
        }

        return [
            'subtotal'        => round($subtotal, 2),
            'tax_amount'      => round($taxAmount, 2),
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    private function computeLine(array $item): array
    {
        $lineSubtotal = round((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0), 2);
        $discountAmount = round((float) ($item['discount_amount'] ?? 0), 2);
        $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== '' && $item['tax_rate'] !== null ? (float) $item['tax_rate'] : null;
        $taxAmount = $taxRate !== null
            ? round(($lineSubtotal - $discountAmount) * $taxRate / 100, 2)
            : round((float) ($item['tax_amount'] ?? 0), 2);

        return [$lineSubtotal, $discountAmount, $taxAmount];
    }

    /**
     * @return array<string, int> maps each submitted item's client-side
     *         `client_ref` (the Alpine item's stable `_localKey`) to its
     *         persisted row id — a brand-new item has no id until this runs,
     *         and document uploads (added in a later phase) need a real,
     *         persisted QuotationItem id to attach media to, so the client
     *         needs a way to learn the id an autosave just created.
     */
    private function syncItems(Quotation $quotation, array $items): array
    {
        $keepIds = [];
        $clientRefMap = [];

        foreach (array_values($items) as $item) {
            $isOptionalAddon = (bool) ($item['is_optional_addon'] ?? false);

            if (! empty($item['offers']) && is_array($item['offers'])) {
                $primaryOffer = collect($item['offers'])->firstWhere('is_primary', true) ?? ($item['offers'][0] ?? null);
                if ($primaryOffer) {
                    $item['item_name'] = $primaryOffer['product_name'] ?? ($item['item_name'] ?? '');
                    $item['offered_listing_id'] = $primaryOffer['marketplace_product_id'] ?? ($item['offered_listing_id'] ?? null);
                    $item['offered_variant_id'] = $primaryOffer['offered_variant_id'] ?? ($item['offered_variant_id'] ?? null);
                    $item['response_method'] = $primaryOffer['offer_method'] ?? ($item['response_method'] ?? null);
                    $item['description'] = $primaryOffer['description'] ?? ($item['description'] ?? null);
                    $item['quantity'] = $primaryOffer['quantity'] ?? ($item['quantity'] ?? 1);
                    $item['unit_id'] = $primaryOffer['unit_id'] ?? ($item['unit_id'] ?? null);
                    $item['custom_unit'] = $primaryOffer['custom_unit'] ?? ($item['custom_unit'] ?? null);
                    $item['unit_price'] = $primaryOffer['unit_price'] ?? ($item['unit_price'] ?? 0);
                    $item['tax_rate'] = isset($primaryOffer['tax_rate']) && $primaryOffer['tax_rate'] !== '' && $primaryOffer['tax_rate'] !== null ? (float) $primaryOffer['tax_rate'] : ($item['tax_rate'] ?? null);
                    $item['discount_amount'] = isset($primaryOffer['discount']) ? (float) $primaryOffer['discount'] : ($item['discount_amount'] ?? 0);
                    $item['lead_time_days'] = $primaryOffer['delivery_time'] ?? ($item['lead_time_days'] ?? null);
                    if (isset($primaryOffer['specifications'])) {
                        $item['specs'] = $primaryOffer['specifications'];
                    }
                }
            }

            [$lineSubtotal, $discountAmount, $taxAmount] = $this->computeLine($item);
            $lineTotal = round($lineSubtotal - $discountAmount + $taxAmount, 2);
            $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== '' && $item['tax_rate'] !== null ? (float) $item['tax_rate'] : null;

            $attributes = [
                'quotation_id'       => $quotation->id,
                'rfq_item_id'        => $isOptionalAddon ? null : ($item['rfq_item_id'] ?? null),
                'offered_listing_id' => $item['offered_listing_id'] ?? null,
                'offered_variant_id' => $item['offered_variant_id'] ?? null,
                'is_alternative'     => (bool) ($item['is_alternative'] ?? false),
                'is_optional_addon'  => $isOptionalAddon,
                'response_method'    => $item['response_method'] ?? null,
                'item_name'          => $item['item_name'],
                'description'        => $item['description'] ?? null,
                'quantity'           => $item['quantity'],
                'unit_id'            => $item['unit_id'] ?? null,
                'custom_unit'        => $item['custom_unit'] ?? null,
                'unit_price'         => $item['unit_price'],
                'tax_rate'           => $taxRate,
                'tax_amount'         => $taxAmount,
                'discount_amount'    => $discountAmount,
                'line_total'         => $lineTotal,
                'lead_time_days'     => $item['lead_time_days'] ?? null,
                'specs'              => $item['specs'] ?? null,
            ];

            $row = null;
            if (! empty($item['id'])) {
                $row = QuotationItem::where('quotation_id', $quotation->id)->find($item['id']);
            }

            if ($row) {
                $row->update($attributes);
            } else {
                $row = QuotationItem::create($attributes);
            }

            $this->syncItemAttributeValues($row, $item['attribute_values'] ?? []);
            $this->syncItemOffers($row, $item['offers'] ?? []);

            $keepIds[] = $row->id;
            if (! empty($item['client_ref'])) {
                $clientRefMap[$item['client_ref']] = $row->id;
            }
        }

        QuotationItem::where('quotation_id', $quotation->id)->whereNotIn('id', $keepIds)->delete();

        return $clientRefMap;
    }

    /**
     * Direct adaptation of RfqService::syncItemAttributeValues() — same
     * per-input-type switch and "__other__" sentinel handling, but the
     * attribute set is driven by the BUYER's requested category
     * ($item->rfqItem->category_id), not whatever category the supplier's
     * offered listing happens to belong to, so the buyer-vs-supplier
     * comparison always lines up against the same taxonomy (spec §38-39).
     * Optional add-ons have no rfq_item, so they carry no structured
     * attribute values (only specs/description) — nothing to compare them
     * against.
     */
    private function syncItemAttributeValues(QuotationItem $item, array $attributeValues): void
    {
        $categoryId = $item->rfq_item_id ? $item->rfqItem?->category_id : null;

        if (empty($attributeValues) || ! $categoryId) {
            QuotationItemAttributeValue::where('quotation_item_id', $item->id)->delete();
            return;
        }

        $category = Category::find($categoryId);
        if (! $category) {
            QuotationItemAttributeValue::where('quotation_item_id', $item->id)->delete();
            return;
        }

        $categoryAttributes = $category->attributes()->with('values')->get();
        if ($categoryAttributes->isEmpty() && $category->parent_id) {
            $curr = $category;
            while ($categoryAttributes->isEmpty() && $curr->parent_id) {
                $curr = $curr->parent;
                if ($curr) {
                    $categoryAttributes = $curr->attributes()->with('values')->get();
                }
            }
        }
        $validAttrMap = $categoryAttributes->keyBy('id');

        $processedAttrIds = [];

        foreach ($attributeValues as $attrId => $rawVal) {
            $attrId = (int) $attrId;
            if (! isset($validAttrMap[$attrId])) {
                continue;
            }

            $attr = $validAttrMap[$attrId];
            $processedAttrIds[] = $attrId;

            $saveData = [
                'attribute_value_id' => null,
                'value_text'         => null,
                'value_number'       => null,
                'value_boolean'      => null,
                'value_date'         => null,
                'value_json'         => null,
                'custom_value'       => null,
            ];

            if (is_array($rawVal)) {
                $valueText = isset($rawVal['value_text']) ? trim($rawVal['value_text']) : null;
                $valueNumber = isset($rawVal['value_number']) && $rawVal['value_number'] !== '' ? $rawVal['value_number'] : null;
                $valueBoolean = isset($rawVal['value_boolean']) && $rawVal['value_boolean'] !== '' ? (bool) $rawVal['value_boolean'] : null;
                $valueDate = ! empty($rawVal['value_date']) ? $rawVal['value_date'] : null;
                $valueJson = isset($rawVal['value_json']) ? (is_array($rawVal['value_json']) ? $rawVal['value_json'] : json_decode($rawVal['value_json'], true)) : null;
                $customValue = isset($rawVal['custom_value']) ? trim($rawVal['custom_value']) : null;
                $customValue = ($customValue !== null && $customValue !== '') ? $customValue : null;
                // "__other__" is the form's sentinel for "supplier picked Other" —
                // it must never be cast/stored as a real attribute_value_id.
                $isOtherSelected = ($rawVal['attribute_value_id'] ?? null) === '__other__';
                $attributeValueId = (! $isOtherSelected && ! empty($rawVal['attribute_value_id']))
                    ? (int) $rawVal['attribute_value_id']
                    : null;
            } else {
                $valueText = is_string($rawVal) ? trim($rawVal) : null;
                $valueNumber = null;
                $valueBoolean = null;
                $valueDate = null;
                $valueJson = null;
                $customValue = null;
                $isOtherSelected = false;
                $attributeValueId = null;
            }

            switch ($attr->input_type) {
                case 'select':
                    if ($isOtherSelected && $customValue !== null) {
                        $saveData['custom_value'] = $customValue;
                    } else {
                        $saveData['attribute_value_id'] = $attributeValueId;
                        if ($attributeValueId) {
                            $valObj = $attr->values->firstWhere('id', $attributeValueId);
                            $saveData['value_text'] = $valObj?->value;
                        }
                    }
                    break;

                case 'multi_select':
                    if (is_array($valueJson) && ! empty($valueJson)) {
                        $cleanJson = array_values(array_filter($valueJson));
                        $saveData['value_json'] = $cleanJson;
                        $saveData['value_text'] = implode(', ', $cleanJson);
                    } elseif ($valueText !== null && $valueText !== '') {
                        $parts = array_values(array_filter(array_map('trim', explode(',', $valueText))));
                        $saveData['value_json'] = $parts;
                        $saveData['value_text'] = implode(', ', $parts);
                    }
                    $saveData['custom_value'] = $customValue;
                    break;

                case 'number':
                    $saveData['value_number'] = is_numeric($valueNumber) ? (float) $valueNumber : (is_numeric($valueText) ? (float) $valueText : null);
                    break;

                case 'boolean':
                    $saveData['value_boolean'] = $valueBoolean;
                    break;

                case 'date':
                    $saveData['value_date'] = $valueDate ?: $valueText;
                    break;

                case 'color':
                    if ($isOtherSelected && $customValue !== null) {
                        $saveData['custom_value'] = $customValue;
                    } else {
                        $saveData['attribute_value_id'] = $attributeValueId;
                        $saveData['value_text'] = $attributeValueId ? $attr->values->firstWhere('id', $attributeValueId)?->value : null;
                    }
                    break;

                case 'textarea':
                case 'text':
                default:
                    $saveData['value_text'] = $valueText;
                    break;
            }

            $hasAnyValue = $saveData['attribute_value_id'] !== null
                || ($saveData['value_text'] !== null && $saveData['value_text'] !== '')
                || $saveData['value_number'] !== null
                || $saveData['value_boolean'] !== null
                || $saveData['value_date'] !== null
                || (! empty($saveData['value_json']))
                || ($saveData['custom_value'] !== null && $saveData['custom_value'] !== '');

            if ($hasAnyValue) {
                QuotationItemAttributeValue::updateOrCreate(
                    ['quotation_item_id' => $item->id, 'attribute_id' => $attrId],
                    $saveData
                );
            } else {
                QuotationItemAttributeValue::where('quotation_item_id', $item->id)->where('attribute_id', $attrId)->delete();
            }
        }

        QuotationItemAttributeValue::where('quotation_item_id', $item->id)->whereNotIn('attribute_id', $processedAttrIds)->delete();
    }

    /**
     * Synchronizes child quotation_item_offers under one QuotationItem (Product Response layer).
     * If no offers array is explicitly sent, seeds a default offer mirroring the QuotationItem
     * for full backward compatibility.
     */
    private function syncItemOffers(QuotationItem $item, array $offers): void
    {
        $keepOfferIds = [];
        $hasPrimary = false;

        if (empty($offers)) {
            // Seed / sync single default offer matching the quotation item
            $firstOffer = $item->offers()->first();
            $offerData = [
                'quotation_item_id'      => $item->id,
                'offer_method'           => $item->response_method ?: 'marketplace',
                'marketplace_product_id' => $item->offered_listing_id,
                'offered_variant_id'     => $item->offered_variant_id,
                'product_name'           => $item->item_name,
                'category_id'            => $item->rfqItem?->category_id,
                'description'            => $item->description,
                'specifications'         => $item->specs,
                'quantity'               => $item->quantity,
                'unit_id'                => $item->unit_id,
                'custom_unit'            => $item->custom_unit,
                'unit_price'             => $item->unit_price,
                'tax_rate'               => $item->tax_rate,
                'tax_amount'             => $item->tax_amount,
                'discount'               => $item->discount_amount,
                'total_price'            => $item->line_total,
                'delivery_time'          => $item->lead_time_days,
                'status'                 => 'draft',
                'is_primary'             => true,
                'is_selected'            => false,
                'sort_order'             => 0,
            ];

            if ($firstOffer) {
                $firstOffer->update($offerData);
                $keepOfferIds[] = $firstOffer->id;
            } else {
                $created = QuotationItemOffer::create($offerData);
                $keepOfferIds[] = $created->id;
            }
        } else {
            foreach ($offers as $idx => $offer) {
                $isPrimary = (bool) ($offer['is_primary'] ?? ($idx === 0));
                if ($isPrimary && ! $hasPrimary) {
                    $hasPrimary = true;
                } elseif ($isPrimary && $hasPrimary) {
                    $isPrimary = false;
                }

                $qty = (float) ($offer['quantity'] ?? $item->quantity ?? 1);
                $price = (float) ($offer['unit_price'] ?? 0);
                $discount = (float) ($offer['discount'] ?? 0);
                $taxRate = isset($offer['tax_rate']) && $offer['tax_rate'] !== '' && $offer['tax_rate'] !== null ? (float) $offer['tax_rate'] : null;
                $taxAmount = $taxRate !== null ? round((($qty * $price) - $discount) * $taxRate / 100, 2) : 0.0;
                $totalPrice = round((($qty * $price) - $discount) + $taxAmount, 2);

                $specs = $offer['specifications'] ?? ($offer['specs'] ?? null);
                if (is_string($specs)) {
                    $decoded = json_decode($specs, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $specs = $decoded;
                    }
                }

                $offerData = [
                    'quotation_item_id'      => $item->id,
                    'offer_method'           => $offer['offer_method'] ?? 'marketplace',
                    'marketplace_product_id' => $offer['marketplace_product_id'] ?? null,
                    'offered_variant_id'     => $offer['offered_variant_id'] ?? null,
                    'product_name'           => $offer['product_name'] ?? $item->item_name,
                    'category_id'            => $offer['category_id'] ?? $item->rfqItem?->category_id,
                    'description'            => $offer['description'] ?? null,
                    'specifications'         => $specs,
                    'quantity'               => $qty,
                    'unit_id'                => $offer['unit_id'] ?? $item->unit_id,
                    'custom_unit'            => $offer['custom_unit'] ?? null,
                    'unit_price'             => $price,
                    'tax_rate'               => $taxRate,
                    'tax_amount'             => $taxAmount,
                    'discount'               => $discount,
                    'total_price'            => $totalPrice,
                    'delivery_time'          => isset($offer['delivery_time']) && $offer['delivery_time'] !== '' ? (int) $offer['delivery_time'] : null,
                    'status'                 => $offer['status'] ?? 'draft',
                    'is_primary'             => $isPrimary,
                    'is_selected'            => (bool) ($offer['is_selected'] ?? false),
                    'sort_order'             => (int) ($offer['sort_order'] ?? $idx),
                ];

                $row = null;
                if (! empty($offer['id'])) {
                    $row = QuotationItemOffer::where('quotation_item_id', $item->id)->find($offer['id']);
                }

                if ($row) {
                    $row->update($offerData);
                } else {
                    $row = QuotationItemOffer::create($offerData);
                }

                $keepOfferIds[] = $row->id;
            }
        }

        QuotationItemOffer::where('quotation_item_id', $item->id)->whereNotIn('id', $keepOfferIds)->delete();
    }

    private function generateQuotationNumber(): string
    {
        $year   = date('Y');
        $latest = Quotation::withTrashed()->where('quotation_number', 'like', "QT-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "QT-{$year}-{$seq}";
    }

    private function buyerRfqUrl(Rfq $rfq): ?string
    {
        return function_exists('route') ? route('buyer.rfqs.show', $rfq) : null;
    }

    private function notifyBuyer(Rfq $rfq, string $message, ?string $url = null): void
    {
        $users = User::whereHas('accountMember', fn ($q) => $q->where('account_id', $rfq->buyer_account_id)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message, $url));
        }
    }
}
