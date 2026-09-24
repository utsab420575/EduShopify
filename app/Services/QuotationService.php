<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\QuotationDeliveryAddress;
use App\Models\QuotationItem;
use App\Models\QuotationItemAttributeValue;
use App\Models\QuotationItemOffer;
use App\Models\QuotationRevision;
use App\Models\QuotationRevisionItem;
use App\Models\QuotationRevisionItemAttributeValue;
use App\Models\QuotationRevisionItemOffer;
use App\Models\QuotationRevisionRequest;
use App\Models\Rfq;
use App\Models\RfqShortlist;
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

    /**
     * Same idea as $lastClientRefItemIds, one level deeper: offer
     * `_localKey`s (globally unique — generateLocalKey() in _form.blade.php)
     * mapped to their persisted quotation_item_offers id, so the client can
     * learn a brand-new Document offer's real id right after an autosave and
     * start uploading files to it. Flat (not nested per item) since offer
     * local keys are already unique across the whole form.
     */
    private array $lastClientRefOfferIds = [];

    public function __construct(
        private SupplierRfqActionService $supplierRfqActions,
        private QuotationActivityService $quotationActivities,
    ) {
    }

    public function saveDraft(Rfq $rfq, Account $supplierAccount, User $user, array $data, ?Quotation $quotation = null): Quotation
    {
        return DB::transaction(function () use ($rfq, $supplierAccount, $user, $data, $quotation) {
            $totals = $this->computeTotals($data['items'] ?? []);
            $shipping = $totals['shipping_amount'];
            $grandTotal = round($totals['subtotal'] - $totals['discount_amount'] + $totals['tax_amount'] + $shipping, 2);

            $attributes = [
                'title' => $data['title'] ?? $quotation?->title,
                'description' => $data['description'] ?? $quotation?->description,
                'currency_code' => $this->resolveCurrency($data['currency_code'] ?? $quotation?->currency_code ?? $rfq->currency_code),
                'current_step' => isset($data['current_step']) ? (int) $data['current_step'] : ($quotation?->current_step ?? 1),
                // Forward-only — never let an autosave regress this even if
                // the client sends a lower value (e.g. a stale tab reloaded
                // after the supplier progressed further in another tab).
                'max_completed_step' => max(
                    isset($data['max_completed_step']) ? (int) $data['max_completed_step'] : 1,
                    $quotation?->max_completed_step ?? 1
                ),
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'shipping_charge' => $shipping,
                'grand_total' => $grandTotal,
                // lead_time_days is intentionally left untouched here — no
                // longer collected at the quotation level (replaced by
                // expected_delivery_date below); still used at the item
                // level and by "clone from previous quotation" matching.
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $quotation?->expected_delivery_date,
                // valid_until/proposal are no longer collected by this form
                // either — falls back to whatever was already there instead
                // of a hardcoded null, so removing those fields from the UI
                // doesn't silently wipe out any pre-existing value.
                'valid_until' => $data['valid_until'] ?? $quotation?->valid_until,
                'warranty_terms' => $data['warranty_terms'] ?? null,
                'support_terms' => $data['support_terms'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'proposal' => $data['proposal'] ?? $quotation?->proposal,
            ];

            $isNew = !$quotation;
            if ($quotation) {
                $quotation->update($attributes);
            } else {
                $quotation = Quotation::create($attributes + [
                    'quotation_number' => $this->generateQuotationNumber(),
                    'rfq_id' => $rfq->id,
                    'supplier_account_id' => $supplierAccount->id,
                    'submitted_by_user_id' => $user->id,
                    'rfq_version_no' => $rfq->current_version_no,
                    'current_revision_no' => 0,
                    'status' => 'draft',
                ]);
            }

            if ($isNew || !$quotation->activities()->where('activity_type', 'drafted')->exists()) {
                $this->quotationActivities->record($quotation, 'drafted', 'Quotation draft created.', 'supplier', $user->id);
            }

            // One-time only, right when the quotation row itself is first
            // created — never re-run on later saves, which is what actually
            // makes the copy a starting point rather than a live sync: the
            // supplier can freely edit/replace these from that point on
            // without ever touching rfqs/rfq_delivery_addresses again.
            if ($isNew) {
                $this->copyDeliveryAddressesFromRfq($quotation, $rfq);
            }

            // Only when the key is explicitly present — this whole form is
            // one Alpine app covering every step, so a real Step 2 save always
            // includes it, but internal saveDraft() calls that only ever
            // touch items (e.g. QuotationController::create()'s initial
            // header-only save) must not wipe out the addresses just copied
            // above by treating "key absent" as "supplier cleared everything".
            if (array_key_exists('delivery_addresses', $data)) {
                $this->syncDeliveryAddresses($quotation, $data['delivery_addresses'] ?? []);
            }

            $this->lastClientRefItemIds = $this->syncItems($quotation, $data['items'] ?? []);

            return $quotation->fresh(['items.attributeValues', 'deliveryAddresses']);
        });
    }

    /**
     * Same as getLastClientRefItemIds(), for offers — see
     * $lastClientRefOfferIds.
     */
    public function getLastClientRefOfferIds(): array
    {
        return $this->lastClientRefOfferIds;
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
    public function submitDraft(Quotation $quotation, bool $acknowledgeVersionChange = false, ?User $user = null): Quotation
    {
        $quotation->loadMissing(['rfq.items', 'items']);
        $rfq = $quotation->rfq;

        $this->assertItems($rfq, $quotation->supplier_account_id, $quotation->items);

        if ($quotation->rfq_version_no !== $rfq->current_version_no && !$acknowledgeVersionChange) {
            throw ValidationException::withMessages([
                'rfq_version' => "The RFQ has changed from version {$quotation->rfq_version_no} to version {$rfq->current_version_no} since you started this quotation. Review the changes before submitting.",
            ]);
        }

        return DB::transaction(function () use ($quotation, $rfq, $user) {
            $quotation->update([
                'rfq_version_no'      => $rfq->current_version_no,
                'current_revision_no' => 1,
                'status'              => 'submitted',
                'submitted_at'        => now(),
            ]);

            $rfq->increment('quotations_count');

            // First submission is revision #1's immutable snapshot — see
            // revise(), which is the only other place a later snapshot gets
            // written, never this method again (a quotation only ever
            // passes through submitDraft() once; undoSubmit() + a later
            // resubmit both go through this same path, which is exactly
            // why undoSubmit() must clear out any prior revision row first).
            $resolvedUserId = $user?->id ?? auth()->id() ?? $quotation->submitted_by_user_id;
            $quotation = $quotation->fresh(['items.attributeValues', 'items.offers']);
            $this->snapshotRevision($quotation, $user ?? $quotation->submittedBy);

            RfqSupplierQueue::where('rfq_id', $rfq->id)
                ->where('supplier_account_id', $quotation->supplier_account_id)
                ->update(['status' => 'quotation_submitted']);

            $this->supplierRfqActions->record($rfq, $quotation->supplierAccount, 'quoted');
            $this->quotationActivities->record($quotation, 'submitted', null, 'supplier', $resolvedUserId);

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

        return $this->submitDraft($draft, false, $user);
    }

    public function revise(Quotation $quotation, array $data, ?User $user = null): Quotation
    {
        $this->assertItems($quotation->rfq, $quotation->supplier_account_id, $data['items'] ?? []);

        return DB::transaction(function () use ($quotation, $data, $user) {
            $totals = $this->computeTotals($data['items']);
            $shipping = $totals['shipping_amount'];
            $grandTotal = round($totals['subtotal'] - $totals['discount_amount'] + $totals['tax_amount'] + $shipping, 2);

            $quotation->update([
                'current_revision_no' => $quotation->current_revision_no + 1,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'shipping_charge' => $shipping,
                'grand_total' => $grandTotal,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $quotation->expected_delivery_date,
                'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
                'warranty_terms' => $data['warranty_terms'] ?? $quotation->warranty_terms,
                'support_terms' => $data['support_terms'] ?? $quotation->support_terms,
                'payment_terms' => $data['payment_terms'] ?? $quotation->payment_terms,
                'proposal' => $data['proposal'] ?? $quotation->proposal,
                'rfq_version_no' => $quotation->rfq->current_version_no,
                'status' => 'revised',
                'revised_at' => now(),
            ]);

            $this->syncItems($quotation, $data['items']);

            QuotationRevisionRequest::where('quotation_id', $quotation->id)
                ->where('status', 'pending')
                ->update(['status' => 'revised', 'responded_at' => now()]);

            $quotation = $quotation->fresh(['items.attributeValues', 'items.offers']);
            $this->snapshotRevision($quotation, $user ?? $quotation->submittedBy, $data['change_summary'] ?? null);

            $this->quotationActivities->record($quotation, 'quotation_updated', $data['change_summary'] ?? null, 'supplier', ($user ?? $quotation->submittedBy)?->id);

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

            // Every offer under this item now has its own attribute-value set
            // (see syncOfferAttributeValues()), but the revision snapshot here
            // still only freezes the PRIMARY offer's — quotation_revision_item_
            // attribute_values remains scoped to one set per revision item,
            // same as before this change. Alternatives' full structured
            // answers are still preserved in the revision via each
            // QuotationRevisionItemOffer's own copied `specifications` JSON
            // (see the offer loop below), just not duplicated into this
            // relational table too.
            $primaryOfferId = $item->offers->firstWhere('is_primary', true)?->id;
            foreach ($item->attributeValues->where('quotation_item_offer_id', $primaryOfferId) as $value) {
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
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
            'proposal' => $reason ? trim(($quotation->proposal ?? '') . "\n\n[Withdrawn: {$reason}]") : $quotation->proposal,
        ]);

        $this->notifyBuyer($quotation->rfq, "A supplier withdrew their quotation for \"{$quotation->rfq->title}\".", $this->buyerRfqUrl($quotation->rfq));

        return $quotation;
    }

    /**
     * The inverse of submitDraft() — pulls a submitted quotation back to
     * 'draft' so the supplier can keep editing before resubmitting. Distinct
     * from withdraw(): withdraw() is a terminal opt-out (stays 'withdrawn',
     * never editable again); this restores the same editable state a fresh
     * draft is in. quotations_count/RfqSupplierQueue are only ever bumped
     * once, by the original submitDraft() call (revise() doesn't touch
     * either), so it's safe to unwind them here exactly once.
     */
    public function undoSubmit(Quotation $quotation, ?User $user = null): Quotation
    {
        $hasActiveAward = $quotation->status === 'awarded'
            || $quotation->award()->whereIn('status', ['pending_supplier_response', 'accepted'])->exists()
            || ($quotation->rfq && $quotation->rfq->awards()->whereIn('status', ['pending_supplier_response', 'accepted'])->exists());

        if ($hasActiveAward) {
            throw new \DomainException('Cannot undo submission: this quotation or RFQ has already been awarded.');
        }

        return DB::transaction(function () use ($quotation, $user) {
            $rfq = $quotation->rfq;

            // Wipe the revision trail this submission created (cascades to
            // quotation_revision_items/*_offers/*_attribute_values at the DB
            // level) — undoSubmit() fully reverts to the same "never
            // submitted" state a fresh draft is in, so a later resubmit goes
            // through submitDraft()'s normal revision_no=1 snapshot path
            // instead of colliding with a leftover row on
            // quotation_revisions' (quotation_id, revision_no) unique index.
            $quotation->revisions()->delete();

            // Also clean up any cancelled or rejected awards on this quotation
            // so unique(quotation_id) won't block future awards if resubmitted
            $quotation->award()->whereIn('status', ['cancelled', 'rejected_by_supplier'])->delete();

            // If quotation was shortlisted, remove from shortlists since it is now drafted
            RfqShortlist::where('rfq_id', $rfq->id)
                ->where('quotation_id', $quotation->id)
                ->delete();

            $quotation->update([
                'status'              => 'draft',
                'submitted_at'        => null,
                'current_revision_no' => 0,
            ]);

            $rfq->decrement('quotations_count');

            RfqSupplierQueue::where('rfq_id', $rfq->id)
                ->where('supplier_account_id', $quotation->supplier_account_id)
                ->update(['status' => 'seen']);

            $resolvedUserId = $user?->id ?? auth()->id() ?? $quotation->submitted_by_user_id;
            $this->quotationActivities->record($quotation, 'unsubmitted', null, 'supplier', $resolvedUserId);

            $this->notifyBuyer($rfq, "A supplier pulled back their quotation for \"{$rfq->title}\" to make changes.", $this->buyerRfqUrl($rfq));

            return $quotation;
        });
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
        $source->loadMissing(['items.rfqItem', 'items.attributeValues', 'items.offers']);
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

        usort($pairs, fn($a, $b) => $b['score'] <=> $a['score']);

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

            // Attribute values are per-offer now — carry forward the source
            // item's PRIMARY offer's set, same scope this clone feature has
            // always used (it only ever saw one set per item before offers
            // had their own attribute values).
            $sourcePrimaryOfferId = $sourceItem->offers->firstWhere('is_primary', true)?->id;

            $matches[$targetItem->id] = [
                'unit_price' => $sourceItem->unit_price,
                'tax_rate' => $sourceItem->tax_rate,
                'discount_amount' => $sourceItem->discount_amount,
                'lead_time_days' => $sourceItem->lead_time_days,
                'description' => $sourceItem->description,
                'attribute_values' => $sameCategory
                    ? $sourceItem->attributeValues->where('quotation_item_offer_id', $sourcePrimaryOfferId)->mapWithKeys(fn($v) => [
                        $v->attribute_id => [
                            'attribute_value_id' => $v->attribute_value_id,
                            'custom_value' => $v->custom_value,
                            'value_text' => $v->value_text,
                            'value_number' => $v->value_number,
                            'value_boolean' => $v->value_boolean,
                            'value_date' => $v->value_date,
                            'value_json' => $v->value_json,
                        ]
                    ])->all()
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
        $field = fn($item, string $key) => is_array($item) ? ($item[$key] ?? null) : $item->{$key};

        $requestedItems = $items->reject(fn($i) => (bool) $field($i, 'is_optional_addon'));

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Quote at least one RFQ item.']);
        }

        if (!$rfq->allow_partial_quotation) {
            $rfqItemIds = $rfq->items->pluck('id')->all();
            $quotedRfqItemIds = $requestedItems->map(fn($i) => $field($i, 'rfq_item_id'))->filter()->all();

            if (count(array_diff($rfqItemIds, $quotedRfqItemIds)) > 0) {
                throw ValidationException::withMessages(['items' => 'This RFQ does not allow partial quotations — quote every item.']);
            }
        }

        if (!$rfq->allow_alternative_products && $items->contains(fn($i) => (bool) $field($i, 'is_alternative'))) {
            throw ValidationException::withMessages(['items' => 'This RFQ does not allow alternative products.']);
        }

        $listingIds = $items->flatMap(function ($item) use ($field) {
            $ids = [];
            $offeredId = $field($item, 'offered_listing_id');
            if ($offeredId) {
                $ids[] = $offeredId;
            }
            $offers = $field($item, 'offers');
            if (is_iterable($offers)) {
                foreach ($offers as $offer) {
                    $pid = is_array($offer) ? ($offer['marketplace_product_id'] ?? null) : ($offer->marketplace_product_id ?? null);
                    if ($pid) {
                        $ids[] = $pid;
                    }
                }
            }

            return $ids;
        })->filter()->unique();

        if ($listingIds->isNotEmpty()) {
            $validCount = Listing::whereIn('id', $listingIds)
                ->where(function ($q) use ($supplierAccountId) {
                    $q->where('approval_status', 'approved')
                        ->orWhere('supplier_account_id', $supplierAccountId);
                })
                ->count();
            if ($validCount !== $listingIds->count()) {
                throw ValidationException::withMessages(['items' => 'One of the offered marketplace products is no longer available.']);
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
    /**
     * Determines whether an offer represents an actual response.
     * Prevents persisting uncompleted or empty placeholders into database.
     */
    private function isValidOffer(array $offer): bool
    {
        $method = $offer['offer_method'] ?? 'marketplace';

        if ($method === 'marketplace') {
            return !empty($offer['marketplace_product_id']) || (!empty($offer['product_name']) && isset($offer['unit_price']) && $offer['unit_price'] !== '');
        }

        if ($method === 'document') {
            $hasPrice = isset($offer['unit_price']) && $offer['unit_price'] !== '' && $offer['unit_price'] !== null && (float) $offer['unit_price'] > 0;
            $hasDocs = !empty($offer['documents']) || !empty($offer['_documents']);
            $hasExistingMedia = !empty($offer['id']) && QuotationItemOffer::where('id', $offer['id'])->whereHas('media')->exists();
            $hasDescription = !empty($offer['description']);
            // Only the offer actively being uploaded to (uploadOfferDocument()
            // sets _uploadPreparing BEFORE its bootstrap autosave, precisely so
            // this flag — not the always-present client_ref — is what signals
            // "the supplier just picked a file," letting that one autosave
            // through to obtain a real id to attach the file to. Merely adding
            // a Document offer and never selecting a file must not persist it.
            $isUploadPreparing = !empty($offer['_uploadPreparing']);
            return $hasPrice || $hasDocs || $hasExistingMedia || $hasDescription || $isUploadPreparing;
        }

        if ($method === 'custom' || $method === 'copy_spec') {
            // Once it already has a row, keep it — the only way an offer is
            // meant to disappear is the trash-icon removeOffer() (which
            // deletes it directly and immediately). A mid-edit blank price
            // must never silently delete an already-persisted offer via this
            // filter on the next autosave.
            if (!empty($offer['id'])) {
                return true;
            }

            // Price is the one field "Copy buyer specifications" (checked by
            // default on a brand-new Custom offer) never auto-fills, so it's
            // a safe gate on its own — a legitimate custom offer can be just
            // a name and a price, nothing else required.
            $hasPrice = isset($offer['unit_price']) && $offer['unit_price'] !== '' && $offer['unit_price'] !== null;
            if ($hasPrice) {
                return true;
            }

            // No price yet — category_id/specifications alone are NOT a safe
            // signal here, because the default copy-on-add auto-fills those
            // too, before the supplier has done anything. _hasUserEdited is
            // set client-side only by a genuine change after the offer card
            // mounted (see the $watch in _offer-card.blade.php, which
            // captures its baseline right after that auto-copy already ran) —
            // so it's what actually tells "supplier manually picked a
            // category / typed an attribute value" apart from the untouched
            // default. Without it, a same-priced-blank auto-copied offer must
            // stay unsaved.
            if (empty($offer['_hasUserEdited'])) {
                return false;
            }

            $hasCategory = !empty($offer['category_id']);
            $hasSpecs = !empty($offer['specifications']) || !empty($offer['specs']);
            $hasDescription = !empty($offer['description']);

            return $hasCategory || $hasSpecs || $hasDescription;
        }

        return false;
    }

    private function computeTotals(array $items): array
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $discountAmount = 0.0;
        // Shipping now lives per-offer (quotation_item_offers.shipping_charge,
        // entered in Step 1) rather than as one flat quotation-level value —
        // this sums each item's PRIMARY offer's shipping charge into the
        // aggregate saveDraft() writes to quotations.shipping_charge, so
        // every existing reader of that column (revision snapshots, the
        // buyer comparison view, purchase-order generation) keeps working
        // unchanged even though it's now computed, not directly editable.
        $shippingAmount = 0.0;

        foreach ($items as $item) {
            $isOptionalAddon = (bool) ($item['is_optional_addon'] ?? false);
            $hasExplicitOffers = array_key_exists('offers', $item);

            $validOffers = [];
            if ($hasExplicitOffers && is_array($item['offers'])) {
                $validOffers = array_values(array_filter($item['offers'], [$this, 'isValidOffer']));
            }

            $hasOffers = $hasExplicitOffers
                ? (!empty($validOffers))
                : (isset($item['unit_price']) && $item['unit_price'] !== '' && $item['unit_price'] !== null);

            if (!$isOptionalAddon && !$hasOffers) {
                continue;
            }

            if ($hasExplicitOffers && !empty($validOffers)) {
                $primaryOffer = collect($validOffers)->firstWhere('is_primary', true) ?? ($validOffers[0] ?? null);
                if ($primaryOffer) {
                    $item['quantity'] = isset($primaryOffer['quantity']) && is_numeric($primaryOffer['quantity']) ? (float) $primaryOffer['quantity'] : ($item['quantity'] ?? 1);
                    $item['unit_price'] = isset($primaryOffer['unit_price']) && is_numeric($primaryOffer['unit_price']) ? (float) $primaryOffer['unit_price'] : ($item['unit_price'] ?? 0);
                    $item['discount_amount'] = isset($primaryOffer['discount']) && is_numeric($primaryOffer['discount']) ? (float) $primaryOffer['discount'] : ($item['discount_amount'] ?? 0);
                    $item['tax_rate'] = isset($primaryOffer['tax_rate']) && $primaryOffer['tax_rate'] !== '' && $primaryOffer['tax_rate'] !== null ? (float) $primaryOffer['tax_rate'] : ($item['tax_rate'] ?? null);
                    $shippingAmount += isset($primaryOffer['shipping_charge']) && is_numeric($primaryOffer['shipping_charge']) ? (float) $primaryOffer['shipping_charge'] : 0.0;
                }
            }

            [$lineSubtotal, $lineDiscount, $lineTax] = $this->computeLine($item);
            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $discountAmount += $lineDiscount;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'shipping_amount' => round($shippingAmount, 2),
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
        $this->lastClientRefOfferIds = [];

        foreach (array_values($items) as $item) {
            $isOptionalAddon = (bool) ($item['is_optional_addon'] ?? false);
            $hasExplicitOffers = array_key_exists('offers', $item);

            $validOffers = [];
            if ($hasExplicitOffers && is_array($item['offers'])) {
                $validOffers = array_values(array_filter($item['offers'], [$this, 'isValidOffer']));
            }

            $hasOffers = $hasExplicitOffers
                ? (!empty($validOffers))
                : (isset($item['unit_price']) && $item['unit_price'] !== '' && $item['unit_price'] !== null);

            // Lazy creation: Never create a quotation_item row for an RFQ requirement
            // unless the supplier has actually created at least one offer for it.
            if (!$isOptionalAddon && !$hasOffers) {
                continue;
            }

            if ($hasExplicitOffers && !empty($validOffers)) {
                $primaryOffer = collect($validOffers)->firstWhere('is_primary', true) ?? ($validOffers[0] ?? null);
                if ($primaryOffer) {
                    $item['item_name'] = !empty($primaryOffer['product_name']) ? $primaryOffer['product_name'] : ($item['item_name'] ?? '');
                    $item['offered_listing_id'] = $primaryOffer['marketplace_product_id'] ?? ($item['offered_listing_id'] ?? null);
                    $item['offered_variant_id'] = $primaryOffer['offered_variant_id'] ?? ($item['offered_variant_id'] ?? null);
                    $item['response_method'] = $primaryOffer['offer_method'] ?? ($item['response_method'] ?? null);
                    $item['description'] = $primaryOffer['description'] ?? ($item['description'] ?? null);
                    $item['quantity'] = isset($primaryOffer['quantity']) && is_numeric($primaryOffer['quantity']) ? (float) $primaryOffer['quantity'] : ($item['quantity'] ?? 1);
                    $item['unit_id'] = $primaryOffer['unit_id'] ?? ($item['unit_id'] ?? null);
                    $item['custom_unit'] = $primaryOffer['custom_unit'] ?? ($item['custom_unit'] ?? null);
                    $item['unit_price'] = isset($primaryOffer['unit_price']) && is_numeric($primaryOffer['unit_price']) ? (float) $primaryOffer['unit_price'] : ($item['unit_price'] ?? 0);
                    $item['tax_rate'] = isset($primaryOffer['tax_rate']) && $primaryOffer['tax_rate'] !== '' && $primaryOffer['tax_rate'] !== null ? (float) $primaryOffer['tax_rate'] : ($item['tax_rate'] ?? null);
                    $item['discount_amount'] = isset($primaryOffer['discount']) && is_numeric($primaryOffer['discount']) ? (float) $primaryOffer['discount'] : ($item['discount_amount'] ?? 0);
                    $item['lead_time_days'] = $primaryOffer['delivery_time'] ?? ($item['lead_time_days'] ?? null);
                    if (isset($primaryOffer['specifications'])) {
                        $item['specs'] = $primaryOffer['specifications'];
                    }
                    // attribute_values/category_id are no longer collapsed onto
                    // the item here — each offer (not just the primary) now
                    // persists its own structured attribute values, handled
                    // per-offer inside syncItemOffers() -> syncOfferAttributeValues().
                }
            }

            [$lineSubtotal, $discountAmount, $taxAmount] = $this->computeLine($item);
            $lineTotal = round($lineSubtotal - $discountAmount + $taxAmount, 2);
            $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== '' && $item['tax_rate'] !== null ? (float) $item['tax_rate'] : null;

            $attributes = [
                'quotation_id' => $quotation->id,
                'rfq_item_id' => $isOptionalAddon ? null : ($item['rfq_item_id'] ?? null),
                'offered_listing_id' => $item['offered_listing_id'] ?? null,
                'offered_variant_id' => $item['offered_variant_id'] ?? null,
                'is_alternative' => (bool) ($item['is_alternative'] ?? false),
                'is_optional_addon' => $isOptionalAddon,
                'response_method' => $item['response_method'] ?? null,
                'item_name' => !empty($item['item_name']) ? (string) $item['item_name'] : ($isOptionalAddon ? 'Optional Add-on' : 'Item'),
                'description' => $item['description'] ?? null,
                'quantity' => isset($item['quantity']) && is_numeric($item['quantity']) ? (float) $item['quantity'] : 1.0,
                'unit_id' => $item['unit_id'] ?? null,
                'custom_unit' => $item['custom_unit'] ?? null,
                'unit_price' => isset($item['unit_price']) && is_numeric($item['unit_price']) ? (float) $item['unit_price'] : 0.0,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'line_total' => $lineTotal,
                'lead_time_days' => $item['lead_time_days'] ?? null,
                'specs' => $item['specs'] ?? null,
            ];

            $row = null;
            if (!empty($item['id'])) {
                $row = QuotationItem::where('quotation_id', $quotation->id)->find($item['id']);
            }

            if ($row) {
                $row->update($attributes);
            } else {
                $row = QuotationItem::create($attributes);
            }

            $offerMap = $this->syncItemOffers($row, $hasExplicitOffers ? $validOffers : null);
            foreach ($offerMap as $clientRef => $offerId) {
                $this->lastClientRefOfferIds[$clientRef] = $offerId;
            }

            $keepIds[] = $row->id;
            if (!empty($item['client_ref'])) {
                $clientRefMap[$item['client_ref']] = $row->id;
            }
        }

        QuotationItem::where('quotation_id', $quotation->id)->whereNotIn('id', $keepIds)->delete();

        return $clientRefMap;
    }

    /**
     * Direct adaptation of RfqService::syncItemAttributeValues() — same
     * per-input-type switch and "__other__" sentinel handling, but scoped to
     * one INDIVIDUAL OFFER (quotation_item_offer_id), not the Product
     * Response as a whole — every offer under an item (primary or
     * alternative) gets its own structured attribute values now, driven by
     * that offer's own category (falling back to the buyer's requested
     * category, $item->rfqItem->category_id, only when the offer has none of
     * its own — e.g. a Document offer). Optional add-ons have no rfq_item,
     * but can still carry a custom offer category, so they're not excluded
     * here on that basis alone.
     */
    private function syncOfferAttributeValues(QuotationItemOffer $offer, QuotationItem $item, array $attributeValues, ?int $categoryId = null): void
    {
        $categoryId = $categoryId ?: ($item->rfq_item_id ? $item->rfqItem?->category_id : null);

        if (empty($attributeValues) || !$categoryId) {
            QuotationItemAttributeValue::where('quotation_item_offer_id', $offer->id)->delete();
            return;
        }

        $category = Category::find($categoryId);
        if (!$category) {
            QuotationItemAttributeValue::where('quotation_item_offer_id', $offer->id)->delete();
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
            if (!isset($validAttrMap[$attrId])) {
                continue;
            }

            $attr = $validAttrMap[$attrId];
            $processedAttrIds[] = $attrId;

            $saveData = [
                'attribute_value_id' => null,
                'value_text' => null,
                'value_number' => null,
                'value_boolean' => null,
                'value_date' => null,
                'value_json' => null,
                'custom_value' => null,
            ];

            if (is_array($rawVal)) {
                $valueText = isset($rawVal['value_text']) ? trim($rawVal['value_text']) : null;
                $valueNumber = isset($rawVal['value_number']) && $rawVal['value_number'] !== '' ? $rawVal['value_number'] : null;
                $valueBoolean = isset($rawVal['value_boolean']) && $rawVal['value_boolean'] !== '' ? (bool) $rawVal['value_boolean'] : null;
                $valueDate = !empty($rawVal['value_date']) ? $rawVal['value_date'] : null;
                $valueJson = isset($rawVal['value_json']) ? (is_array($rawVal['value_json']) ? $rawVal['value_json'] : json_decode($rawVal['value_json'], true)) : null;
                $customValue = isset($rawVal['custom_value']) ? trim($rawVal['custom_value']) : null;
                $customValue = ($customValue !== null && $customValue !== '') ? $customValue : null;
                // "__other__" is the form's sentinel for "supplier picked Other" —
                // it must never be cast/stored as a real attribute_value_id.
                $isOtherSelected = ($rawVal['attribute_value_id'] ?? null) === '__other__';
                $attributeValueId = (!$isOtherSelected && !empty($rawVal['attribute_value_id']))
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
                    if (is_array($valueJson) && !empty($valueJson)) {
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
                || (!empty($saveData['value_json']))
                || ($saveData['custom_value'] !== null && $saveData['custom_value'] !== '');

            if ($hasAnyValue) {
                QuotationItemAttributeValue::updateOrCreate(
                    ['quotation_item_offer_id' => $offer->id, 'attribute_id' => $attrId],
                    $saveData
                );
            } else {
                QuotationItemAttributeValue::where('quotation_item_offer_id', $offer->id)->where('attribute_id', $attrId)->delete();
            }
        }

        QuotationItemAttributeValue::where('quotation_item_offer_id', $offer->id)->whereNotIn('attribute_id', $processedAttrIds)->delete();
    }

    /**
     * Synchronizes child quotation_item_offers under one QuotationItem (Product Response layer).
     * If no offers array is explicitly sent, seeds a default offer mirroring the QuotationItem
     * for full backward compatibility.
     *
     * @return array<string, int> maps each submitted offer's client-side
     *         `client_ref` (the Alpine offer's stable `_localKey`) to its
     *         persisted row id — a brand-new Document offer has no id until
     *         this runs, and the client needs one before it can upload files
     *         to it (see QuotationController::uploadOfferDocument()).
     */
    private function syncItemOffers(QuotationItem $item, ?array $offers): array
    {
        $keepOfferIds = [];
        $clientRefMap = [];
        $hasPrimary = false;

        if ($offers === null) {
            // Legacy shape: caller passed a flat item row without an `offers` array.
            // Synthesize a single default offer matching the legacy row values.
            $offers = [
                [
                    'offer_method' => $item->offered_listing_id ? 'marketplace' : 'custom',
                    'marketplace_product_id' => $item->offered_listing_id,
                    'product_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'currency_code' => $item->currency_code,
                    'is_primary' => true,
                    'is_included_in_bid' => true,
                ]
            ];
        } elseif (empty($offers)) {
            QuotationItemOffer::where('quotation_item_id', $item->id)->delete();
            return [];
        }

        $offers = array_values(array_filter($offers, [$this, 'isValidOffer']));
        if (empty($offers)) {
            QuotationItemOffer::where('quotation_item_id', $item->id)->delete();
            return [];
        }

        // Defense in depth: the client is the source of truth for what
        // offers should exist, but two rows for the same marketplace
        // product (+ variant) under one item is never legitimate — drop
        // any repeat before it ever reaches a create/update, regardless
        // of what caused the client to send it twice.
        $seenMarketplaceKeys = [];
        $offers = array_filter($offers, function ($offer) use (&$seenMarketplaceKeys) {
            $productId = $offer['marketplace_product_id'] ?? null;
            if ($productId === null || $productId === '') {
                return true;
            }
            $key = $productId . ':' . ($offer['offered_variant_id'] ?? '');
            if (isset($seenMarketplaceKeys[$key])) {
                return false;
            }
            $seenMarketplaceKeys[$key] = true;

            return true;
        });

        foreach ($offers as $idx => $offer) {
            $isPrimary = (bool) ($offer['is_primary'] ?? ($idx === 0));
            if ($isPrimary && !$hasPrimary) {
                $hasPrimary = true;
            } elseif ($isPrimary && $hasPrimary) {
                $isPrimary = false;
            }

            $qty = (float) ($offer['quantity'] ?? $item->quantity ?? 1);
            $price = (float) ($offer['unit_price'] ?? 0);
            $discount = (float) ($offer['discount'] ?? 0);
            $shippingCharge = (float) ($offer['shipping_charge'] ?? 0);
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

            // Same JSON-string-or-array duality as specifications above — a
            // native form post sends the hidden [attribute_values] input as
            // a string; autosave's AJAX call sends it as a real array.
            $attributeValues = $offer['attribute_values'] ?? [];
            if (is_string($attributeValues)) {
                $decoded = json_decode($attributeValues, true);
                $attributeValues = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
            }

            $offerData = [
                'quotation_item_id' => $item->id,
                'offer_method' => $offer['offer_method'] ?? 'marketplace',
                'marketplace_product_id' => $offer['marketplace_product_id'] ?? null,
                'offered_variant_id' => $offer['offered_variant_id'] ?? null,
                'product_name' => $offer['product_name'] ?? $item->item_name,
                'category_id' => $offer['category_id'] ?? $item->rfqItem?->category_id,
                'description' => $offer['description'] ?? null,
                'specifications' => $specs,
                'quantity' => $qty,
                'unit_id' => $offer['unit_id'] ?? $item->unit_id,
                'custom_unit' => $offer['custom_unit'] ?? null,
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount' => $discount,
                'shipping_charge' => $shippingCharge,
                'total_price' => $totalPrice,
                'delivery_time' => isset($offer['delivery_time']) && $offer['delivery_time'] !== '' ? (int) $offer['delivery_time'] : null,
                'status' => $offer['status'] ?? 'draft',
                'is_primary' => $isPrimary,
                'is_selected' => (bool) ($offer['is_selected'] ?? false),
                'sort_order' => (int) ($offer['sort_order'] ?? $idx),
            ];

            $row = null;
            if (!empty($offer['id'])) {
                $row = QuotationItemOffer::where('quotation_item_id', $item->id)->find($offer['id']);
            }

            if ($row) {
                $row->update($offerData);
            } else {
                $row = QuotationItemOffer::create($offerData);
            }

            $this->syncOfferAttributeValues($row, $item, $attributeValues, $offerData['category_id']);

            $keepOfferIds[] = $row->id;
            if (!empty($offer['client_ref'])) {
                $clientRefMap[$offer['client_ref']] = $row->id;
            }
        }

        QuotationItemOffer::where('quotation_item_id', $item->id)->whereNotIn('id', $keepOfferIds)->delete();

        return $clientRefMap;
    }

    /**
     * One-time seed, called only when the quotation row is first created —
     * copies the RFQ's own primary delivery address (denormalized directly
     * on rfqs.delivery_country_id/state_id/city_id/delivery_address, per
     * RfqService's own convention) as sort_order 0, then every
     * rfq_delivery_addresses row after it. Plain inserts, not
     * syncDeliveryAddresses()'s delete-and-recreate — there's nothing to
     * replace yet on a brand-new quotation.
     */
    private function copyDeliveryAddressesFromRfq(Quotation $quotation, Rfq $rfq): void
    {
        $sortOrder = 0;
        $rows = [];

        if ($rfq->delivery_country_id || $rfq->delivery_state_id || $rfq->delivery_city_id || $rfq->delivery_address) {
            $rows[] = [
                'quotation_id' => $quotation->id,
                'country_id' => $rfq->delivery_country_id,
                'state_id' => $rfq->delivery_state_id,
                'city_id' => $rfq->delivery_city_id,
                'address' => $rfq->delivery_address,
                'sort_order' => $sortOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($rfq->deliveryAddresses as $address) {
            $rows[] = [
                'quotation_id' => $quotation->id,
                'country_id' => $address->country_id,
                'state_id' => $address->state_id,
                'city_id' => $address->city_id,
                'address' => $address->address,
                'sort_order' => $sortOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            QuotationDeliveryAddress::insert($rows);
        }
    }

    /**
     * Full delete-and-recreate on every save — same convention
     * RfqService::syncDeliveryAddresses() already uses. Runs independently
     * of rfqs/rfq_delivery_addresses from here on; see copyDeliveryAddressesFromRfq()
     * for the one-time seed.
     */
    private function syncDeliveryAddresses(Quotation $quotation, array $addresses): void
    {
        QuotationDeliveryAddress::where('quotation_id', $quotation->id)->delete();

        foreach (array_values($addresses) as $i => $address) {
            if (empty($address['country_id']) && empty($address['state_id']) && empty($address['city_id']) && empty($address['address'])) {
                continue;
            }

            QuotationDeliveryAddress::create([
                'quotation_id' => $quotation->id,
                'country_id' => $address['country_id'] ?? null,
                'state_id' => $address['state_id'] ?? null,
                'city_id' => $address['city_id'] ?? null,
                'address' => $address['address'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    private function generateQuotationNumber(): string
    {
        $year = date('Y');
        $latest = Quotation::withTrashed()->where('quotation_number', 'like', "QT-{$year}-%")->count();
        $seq = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "QT-{$year}-{$seq}";
    }

    private function buyerRfqUrl(Rfq $rfq): ?string
    {
        return function_exists('route') ? route('buyer.rfqs.show', $rfq) : null;
    }

    private function notifyBuyer(Rfq $rfq, string $message, ?string $url = null): void
    {
        $users = User::whereHas('accountMember', fn($q) => $q->where('account_id', $rfq->buyer_account_id)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message, $url));
        }
    }
}
