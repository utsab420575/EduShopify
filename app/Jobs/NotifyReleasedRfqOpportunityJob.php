<?php

namespace App\Jobs;

use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies one supplier about an RFQ opportunity once their queue row's
 * subscription-plan delay has actually elapsed — dispatched with a ->delay()
 * matching rfq_supplier_queue.available_at by RfqQueueService::notifyQueuedSuppliers(),
 * instead of notifying instantly and leaving the supplier unable to open
 * the opportunity (RfqPolicy::viewAsOpportunity() and the supplier
 * opportunities list both gate on the same released() scope).
 */
class NotifyReleasedRfqOpportunityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $rfqId,
        public readonly int $supplierAccountId,
    ) {}

    public function handle(): void
    {
        $rfq = Rfq::find($this->rfqId);
        if (! $rfq) {
            return;
        }

        // Re-check at run time rather than trusting the state captured when
        // this job was scheduled — the row may have been re-queued with a
        // different delay since, or the subscription may have lapsed.
        $released = RfqSupplierQueue::where('rfq_id', $this->rfqId)
            ->where('supplier_account_id', $this->supplierAccountId)
            ->released()
            ->exists();

        if (! $released) {
            return;
        }

        $users = User::whereHas('accountMember', fn ($q) => $q
            ->where('account_id', $this->supplierAccountId)
            ->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification(
                "New RFQ opportunity: \"{$rfq->title}\".",
                route('supplier.opportunities.show', $rfq)
            ));
        }
    }
}
