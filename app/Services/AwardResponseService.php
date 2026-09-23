<?php

namespace App\Services;

use App\Models\Award;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Notifications\DashboardNotification;
use App\Services\QuotationActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Supplier response to an award — the other half of the loop AwardService
 * (buyer side) starts. Acceptance creates the Purchase Order per spec rule 50.
 */
class AwardResponseService
{
    public function __construct(private QuotationActivityService $quotationActivities)
    {
    }

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

            $this->quotationActivities->record($award->quotation()->first(), 'accepted', $note, 'supplier', auth()->id());

            $this->createPurchaseOrder($award);

            $this->notifyBuyer($award, "The supplier accepted the award for \"{$award->rfq->title}\" — a purchase order has been created.");

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

            $this->quotationActivities->record($award->quotation()->first(), 'rejected_by_supplier', $reason, 'supplier', auth()->id());

            $this->notifyBuyer($award, "The supplier declined the award for \"{$rfq->title}\" ({$reason}).");

            return $award->fresh();
        });
    }

    /**
     * Builds the PO strictly from each Product Response's AWARDED offer —
     * the buyer's explicit selection (QuotationItem::selectedOffer) if they
     * made one, else the supplier's own primary offer — never the highest
     * bid and never more than one line per Product Response. Because the
     * buyer's selection can differ in price from whichever offer was
     * primary while quoting, the PO's own totals are recomputed from the
     * awarded offers rather than copied from the quotation's stored
     * (primary-offer-based) totals.
     */
    private function createPurchaseOrder(Award $award): void
    {
        $quotation = $award->quotation()->with(['items.selectedOffer', 'items.primaryOffer'])->first();

        $lines = [];
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $discountAmount = 0.0;

        foreach ($quotation->items as $item) {
            $offer = $item->selectedOffer ?? $item->primaryOffer;

            $lineSubtotal = $offer
                ? round((float) $offer->quantity * (float) $offer->unit_price, 2)
                : round((float) $item->quantity * (float) $item->unit_price, 2);
            $lineDiscount = (float) ($offer->discount ?? $item->discount_amount ?? 0);
            $lineTax = (float) ($offer->tax_amount ?? $item->tax_amount ?? 0);
            $lineTotal = $offer ? round((float) $offer->total_price, 2) : (float) $item->line_total;

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $discountAmount += $lineDiscount;

            $lines[] = [
                'quotation_item_id'        => $item->id,
                'quotation_item_offer_id'  => $offer?->id,
                'item_name'                => $offer->product_name ?? $item->item_name,
                'description'              => $offer->description ?? $item->description,
                'quantity'                 => $offer->quantity ?? $item->quantity,
                'unit_id'                  => $offer->unit_id ?? $item->unit_id,
                'custom_unit'              => $offer->custom_unit ?? $item->custom_unit,
                'unit_price'               => $offer->unit_price ?? $item->unit_price,
                'tax_amount'               => $lineTax,
                'discount_amount'          => $lineDiscount,
                'line_total'               => $lineTotal,
            ];
        }

        $shipping = round((float) $quotation->shipping_charge, 2);
        $grandTotal = round($subtotal - $discountAmount + $taxAmount + $shipping, 2);

        $po = PurchaseOrder::create([
            'po_number'           => $this->generatePoNumber(),
            'award_id'            => $award->id,
            'rfq_id'              => $award->rfq_id,
            'quotation_id'        => $award->quotation_id,
            'buyer_account_id'    => $award->buyer_account_id,
            'supplier_account_id' => $award->supplier_account_id,
            'created_by_user_id'  => $award->awarded_by_user_id,
            'subtotal'            => round($subtotal, 2),
            'tax_amount'          => round($taxAmount, 2),
            'shipping_charge'     => $shipping,
            'discount_amount'     => round($discountAmount, 2),
            'grand_total'         => $grandTotal,
            'currency_code'       => $quotation->currency_code,
            'status'              => 'issued',
            'payment_status'      => 'not_recorded',
            'issued_at'           => now(),
        ]);

        foreach ($lines as $line) {
            PurchaseOrderItem::create(['purchase_order_id' => $po->id] + $line);
        }
    }

    private function generatePoNumber(): string
    {
        $year   = date('Y');
        $latest = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "PO-{$year}-{$seq}";
    }

    private function notifyBuyer(Award $award, string $message): void
    {
        $users = User::whereHas('accountMember', fn ($q) => $q->where('account_id', $award->buyer_account_id)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message, route('buyer.rfqs.show', $award->rfq_id)));
        }
    }
}
