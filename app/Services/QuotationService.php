<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationRevision;
use App\Models\QuotationRevisionItem;
use App\Models\QuotationRevisionRequest;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Supplier-side quotation authoring. The live quotation/quotation_items rows
 * always hold the CURRENT revision; every submit()/revise() also writes an
 * immutable snapshot to quotation_revisions/quotation_revision_items so the
 * buyer can see full revision history (spec 5.5), not just the latest state.
 */
class QuotationService
{
    public function submit(Rfq $rfq, Account $supplierAccount, User $user, array $data): Quotation
    {
        $this->assertItems($rfq, $data['items'] ?? []);

        return DB::transaction(function () use ($rfq, $supplierAccount, $user, $data) {
            $totals = $this->computeTotals($data['items']);

            $quotation = Quotation::create([
                'quotation_number'     => $this->generateQuotationNumber(),
                'rfq_id'               => $rfq->id,
                'supplier_account_id'  => $supplierAccount->id,
                'submitted_by_user_id' => $user->id,
                'rfq_version_no'       => $rfq->current_version_no,
                'current_revision_no'  => 1,
                'subtotal'             => $totals['subtotal'],
                'grand_total'          => $totals['subtotal'],
                'currency_code'        => $this->resolveCurrency($data['currency_code'] ?? $rfq->currency_code),
                'lead_time_days'       => $data['lead_time_days'] ?? null,
                'valid_until'          => $data['valid_until'] ?? null,
                'warranty_terms'       => $data['warranty_terms'] ?? null,
                'support_terms'        => $data['support_terms'] ?? null,
                'payment_terms'        => $data['payment_terms'] ?? null,
                'proposal'             => $data['proposal'] ?? null,
                'status'               => 'submitted',
                'submitted_at'         => now(),
            ]);

            $this->syncItems($quotation, $data['items']);

            $rfq->increment('quotations_count');

            $quotation = $quotation->fresh(['items']);
            $this->snapshotRevision($quotation, $user);

            return $quotation;
        });
    }

    public function revise(Quotation $quotation, array $data, ?User $user = null): Quotation
    {
        $this->assertItems($quotation->rfq, $data['items'] ?? []);

        return DB::transaction(function () use ($quotation, $data, $user) {
            $totals = $this->computeTotals($data['items']);

            $quotation->update([
                'current_revision_no' => $quotation->current_revision_no + 1,
                'subtotal'            => $totals['subtotal'],
                'grand_total'         => $totals['subtotal'],
                'lead_time_days'      => $data['lead_time_days'] ?? $quotation->lead_time_days,
                'valid_until'         => $data['valid_until'] ?? $quotation->valid_until,
                'warranty_terms'      => $data['warranty_terms'] ?? $quotation->warranty_terms,
                'support_terms'       => $data['support_terms'] ?? $quotation->support_terms,
                'payment_terms'       => $data['payment_terms'] ?? $quotation->payment_terms,
                'proposal'            => $data['proposal'] ?? $quotation->proposal,
                'status'              => 'revised',
                'revised_at'          => now(),
            ]);

            $this->syncItems($quotation, $data['items']);

            QuotationRevisionRequest::where('quotation_id', $quotation->id)
                ->where('status', 'pending')
                ->update(['status' => 'revised', 'responded_at' => now()]);

            $quotation = $quotation->fresh(['items']);
            $this->snapshotRevision($quotation, $user ?? $quotation->submittedBy, $data['change_summary'] ?? null);

            return $quotation;
        });
    }

    /**
     * Immutable copy of the quotation's CURRENT state (just after it was set)
     * into quotation_revisions/quotation_revision_items, keyed by the
     * revision number that is now live. Never overwritten.
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
            QuotationRevisionItem::create([
                'quotation_revision_id' => $revision->id,
                'rfq_item_id' => $item->rfq_item_id,
                'offered_listing_id' => $item->offered_listing_id,
                'offered_variant_id' => $item->offered_variant_id,
                'is_alternative' => $item->is_alternative,
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
        }
    }

    public function withdraw(Quotation $quotation, ?string $reason = null): Quotation
    {
        $quotation->update([
            'status'       => 'withdrawn',
            'withdrawn_at' => now(),
            'proposal'     => $reason ? trim(($quotation->proposal ?? '') . "\n\n[Withdrawn: {$reason}]") : $quotation->proposal,
        ]);

        return $quotation;
    }

    private function assertItems(Rfq $rfq, array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'Quote at least one item.']);
        }

        if (! $rfq->allow_partial_quotation) {
            $rfqItemIds = $rfq->items->pluck('id')->all();
            $quotedRfqItemIds = collect($items)->pluck('rfq_item_id')->filter()->all();

            if (count(array_diff($rfqItemIds, $quotedRfqItemIds)) > 0) {
                throw ValidationException::withMessages(['items' => 'This RFQ does not allow partial quotations — quote every item.']);
            }
        }

        if (! $rfq->allow_alternative_products && collect($items)->contains(fn ($i) => ! empty($i['is_alternative']))) {
            throw ValidationException::withMessages(['items' => 'This RFQ does not allow alternative products.']);
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

    private function computeTotals(array $items): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float) $item['quantity'] * (float) $item['unit_price'];
        }

        return ['subtotal' => round($subtotal, 2)];
    }

    private function syncItems(Quotation $quotation, array $items): void
    {
        $keepIds = [];

        foreach (array_values($items) as $item) {
            $lineTotal = round((float) $item['quantity'] * (float) $item['unit_price'], 2);

            $attributes = [
                'quotation_id'       => $quotation->id,
                'rfq_item_id'        => $item['rfq_item_id'] ?? null,
                'is_alternative'     => (bool) ($item['is_alternative'] ?? false),
                'item_name'          => $item['item_name'],
                'description'        => $item['description'] ?? null,
                'quantity'           => $item['quantity'],
                'unit_id'            => $item['unit_id'] ?? null,
                'custom_unit'        => $item['custom_unit'] ?? null,
                'unit_price'         => $item['unit_price'],
                'line_total'         => $lineTotal,
                'lead_time_days'     => $item['lead_time_days'] ?? null,
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

            $keepIds[] = $row->id;
        }

        QuotationItem::where('quotation_id', $quotation->id)->whereNotIn('id', $keepIds)->delete();
    }

    private function generateQuotationNumber(): string
    {
        $year   = date('Y');
        $latest = Quotation::withTrashed()->where('quotation_number', 'like', "QT-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "QT-{$year}-{$seq}";
    }
}
