<?php

namespace App\Services;

use App\Models\Award;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Supplier response to an award — the other half of the loop AwardService
 * (buyer side) starts. Acceptance creates the Purchase Order per spec rule 50.
 */
class AwardResponseService
{
    public function accept(Award $award, ?string $note = null): Award
    {
        return DB::transaction(function () use ($award, $note) {
            // Re-check the award's live status under a row lock inside the
            // transaction, not the caller's already-loaded instance — two
            // concurrent accept requests must not both create a Purchase
            // Order (buyer_dashboard_workflow.md Part 7.1 / 6.4).
            $award = Award::whereKey($award->id)->lockForUpdate()->firstOrFail();

            if (! $award->isAwaitingResponse()) {
                throw ValidationException::withMessages(['status' => 'This award has already been responded to.']);
            }

            $award->update([
                'status'                  => 'accepted',
                'accepted_at'             => now(),
                'responded_at'            => now(),
                'supplier_response_note'  => $note,
            ]);

            $award->rfq()->update(['status' => 'awarded', 'awarded_at' => now()]);
            $award->quotation()->update(['status' => 'awarded', 'decision_at' => now()]);

            $this->createPurchaseOrder($award);

            return $award->fresh();
        });
    }

    public function reject(Award $award, string $reason): Award
    {
        return DB::transaction(function () use ($award, $reason) {
            $award = Award::whereKey($award->id)->lockForUpdate()->firstOrFail();

            if (! $award->isAwaitingResponse()) {
                throw ValidationException::withMessages(['status' => 'This award has already been responded to.']);
            }

            $award->update([
                'status'                     => 'rejected_by_supplier',
                'rejected_at'                => now(),
                'responded_at'               => now(),
                'supplier_rejection_reason'  => $reason,
            ]);

            $rfq = $award->rfq;
            $rfq->update(['status' => $rfq->deadlinePassed() ? 'closed' : 'open']);

            return $award->fresh();
        });
    }

    private function createPurchaseOrder(Award $award): void
    {
        $quotation = $award->quotation()->with('items')->first();

        $po = PurchaseOrder::create([
            'po_number'           => $this->generatePoNumber(),
            'award_id'            => $award->id,
            'rfq_id'              => $award->rfq_id,
            'quotation_id'        => $award->quotation_id,
            'buyer_account_id'    => $award->buyer_account_id,
            'supplier_account_id' => $award->supplier_account_id,
            'created_by_user_id'  => $award->awarded_by_user_id,
            'subtotal'            => $quotation->subtotal,
            'tax_amount'          => $quotation->tax_amount,
            'shipping_charge'     => $quotation->shipping_charge,
            'discount_amount'     => $quotation->discount_amount,
            'grand_total'         => $quotation->grand_total,
            'currency_code'       => $quotation->currency_code,
            'status'              => 'issued',
            'payment_status'      => 'not_recorded',
            'issued_at'           => now(),
        ]);

        foreach ($quotation->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id'  => $po->id,
                'quotation_item_id'  => $item->id,
                'item_name'          => $item->item_name,
                'description'        => $item->description,
                'quantity'           => $item->quantity,
                'unit_id'            => $item->unit_id,
                'custom_unit'        => $item->custom_unit,
                'unit_price'         => $item->unit_price,
                'tax_amount'         => $item->tax_amount,
                'discount_amount'    => $item->discount_amount,
                'line_total'         => $item->line_total,
            ]);
        }
    }

    private function generatePoNumber(): string
    {
        $year   = date('Y');
        $latest = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "PO-{$year}-{$seq}";
    }
}
