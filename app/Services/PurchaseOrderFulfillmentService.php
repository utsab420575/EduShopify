<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Supplier-side fulfilment progression. Buyer-side completion (delivered →
 * completed) is PurchaseOrderService::complete() — kept separate since the two
 * sides own different transitions in the same status enum.
 */
class PurchaseOrderFulfillmentService
{
    private const FORWARD = [
        'issued'              => 'confirmed',
        'confirmed'           => 'in_progress',
        'in_progress'         => 'ready_for_delivery',
        'ready_for_delivery'  => 'delivered',
    ];

    public function advance(PurchaseOrder $purchaseOrder, User $user, ?string $comment = null): PurchaseOrder
    {
        $next = self::FORWARD[$purchaseOrder->status] ?? null;

        if (! $next) {
            throw ValidationException::withMessages(['status' => 'This order cannot be advanced any further by the supplier.']);
        }

        return $this->transition($purchaseOrder, $user, $next, $comment);
    }

    public function cancel(PurchaseOrder $purchaseOrder, User $user, string $reason): PurchaseOrder
    {
        if (in_array($purchaseOrder->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'This order can no longer be cancelled.']);
        }

        return DB::transaction(function () use ($purchaseOrder, $user, $reason) {
            $old = $purchaseOrder->status;

            $purchaseOrder->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
            ]);

            $purchaseOrder->statusHistory()->create([
                'old_status'            => $old,
                'new_status'            => 'cancelled',
                'changed_by_account_id' => $purchaseOrder->supplier_account_id,
                'changed_by_user_id'    => $user->id,
                'comment'               => $reason,
                'created_at'            => now(),
            ]);

            return $purchaseOrder->fresh();
        });
    }

    private function transition(PurchaseOrder $purchaseOrder, User $user, string $newStatus, ?string $comment): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $user, $newStatus, $comment) {
            $old = $purchaseOrder->status;

            $extra = $newStatus === 'delivered' ? ['delivered_at' => now()] : [];
            $extra = $newStatus === 'confirmed' ? $extra + ['confirmed_at' => now()] : $extra;

            $purchaseOrder->update(array_merge(['status' => $newStatus], $extra));

            $purchaseOrder->statusHistory()->create([
                'old_status'            => $old,
                'new_status'            => $newStatus,
                'changed_by_account_id' => $purchaseOrder->supplier_account_id,
                'changed_by_user_id'    => $user->id,
                'comment'               => $comment,
                'created_at'            => now(),
            ]);

            return $purchaseOrder->fresh();
        });
    }
}
