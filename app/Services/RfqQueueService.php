<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use Illuminate\Support\Facades\DB;

/**
 * Fans an RFQ out to rfq_supplier_queue on publish — the gate that drives
 * everything on the supplier side of the marketplace (spec §18.5). Global
 * RFQs reach every active-subscription supplier, delayed by their plan's
 * rfq_delay_minutes; selected-supplier RFQs reach only the invited suppliers
 * with no delay.
 */
class RfqQueueService
{
    public function generateForRfq(Rfq $rfq): void
    {
        DB::transaction(function () use ($rfq) {
            if ($rfq->visibility_type === 'global') {
                $this->queueGlobal($rfq);
            } else {
                $this->queueSelected($rfq);
            }
        });
    }

    private function queueGlobal(Rfq $rfq): void
    {
        Account::query()
            ->marketplace()
            ->where('status', 'active')
            ->whereHas('capabilities', fn ($q) => $q->where('status', 'active')->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier')))
            ->with('activeSubscription.plan')
            ->chunkById(200, function ($suppliers) use ($rfq) {
                foreach ($suppliers as $supplierAccount) {
                    $this->enqueue($rfq, $supplierAccount, 'global');
                }
            });
    }

    private function queueSelected(Rfq $rfq): void
    {
        $rfq->invitedSupplierAccounts()
            ->with('activeSubscription.plan')
            ->get()
            ->each(function ($supplierAccount) use ($rfq) {
                $this->enqueue($rfq, $supplierAccount, 'selected_supplier', delayMinutes: 0);
            });
    }

    private function enqueue(Rfq $rfq, Account $supplierAccount, string $source, ?int $delayMinutes = null): void
    {
        $subscription = $supplierAccount->activeSubscription;
        $plan = $subscription?->plan;

        $eligibilityStatus = match (true) {
            ! $subscription => 'no_subscription',
            $subscription->expires_at && $subscription->expires_at->isPast() => 'subscription_expired',
            default => 'eligible',
        };

        $delay = $delayMinutes ?? (int) ($plan?->rfq_delay_minutes ?? 0);

        RfqSupplierQueue::updateOrCreate(
            ['rfq_id' => $rfq->id, 'supplier_account_id' => $supplierAccount->id],
            [
                'subscription_id'      => $subscription?->id,
                'subscription_plan_id' => $plan?->id,
                'source'               => $source,
                'delay_minutes'        => $delay,
                'available_at'         => now()->addMinutes($delay),
                'eligibility_status'   => $eligibilityStatus,
                'status'               => 'pending',
            ]
        );
    }
}
