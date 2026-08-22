<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    /**
     * Authorized Phase 1 completion (buyer_dashboard_workflow.md Part 7.4):
     * Award Accepted -> PO issued -> fulfilment happens outside the platform
     * -> authorized manual completion -> completed. A PO created from an
     * accepted Award sits at `issued` and Phase 1 does not build any
     * automatic supplier-driven transition out of it, so completion must be
     * reachable directly from `issued`. `delivered` remains accepted too,
     * purely so any legacy/extended-status PO can still be completed safely
     * without rewriting its history — Phase 1 never transitions a new PO
     * into that status itself.
     */
    public function complete(PurchaseOrder $purchaseOrder, User $user): PurchaseOrder
    {
        if (! in_array($purchaseOrder->status, ['issued', 'delivered'], true)) {
            throw ValidationException::withMessages(['status' => 'This purchase order cannot be completed from its current status.']);
        }

        return DB::transaction(function () use ($purchaseOrder, $user) {
            $oldStatus = $purchaseOrder->status;

            $purchaseOrder->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $purchaseOrder->statusHistory()->create([
                'old_status'          => $oldStatus,
                'new_status'          => 'completed',
                'changed_by_account_id' => $purchaseOrder->buyer_account_id,
                'changed_by_user_id'  => $user->id,
                'comment'             => 'Buyer confirmed receipt and completed the order.',
                'created_at'          => now(),
            ]);

            return $purchaseOrder->fresh();
        });
    }
}
