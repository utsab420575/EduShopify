<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    private function checkAccess(User $user, string $capability, string $permission): ?Account
    {
        if (! $user->isActive()) {
            return null;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return null;
        }

        if (! $account->hasActiveCapability($capability)) {
            return null;
        }

        return $user->hasPermissionTo($permission) ? $account : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkAccess($user, 'buyer', 'purchase_order.view_buyer') !== null
            || $this->checkAccess($user, 'supplier', 'purchase_order.view_supplier') !== null;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        $accountId = $user->accountMember?->account_id;

        if ($purchaseOrder->buyer_account_id === $accountId) {
            return $this->checkAccess($user, 'buyer', 'purchase_order.view_buyer') !== null;
        }

        if ($purchaseOrder->supplier_account_id === $accountId) {
            return $this->checkAccess($user, 'supplier', 'purchase_order.view_supplier') !== null;
        }

        return false;
    }

    public function complete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->checkAccess($user, 'buyer', 'purchase_order.complete') !== null
            && $purchaseOrder->buyer_account_id === $user->accountMember?->account_id
            && in_array($purchaseOrder->status, ['issued', 'delivered'], true);
    }

    public function updateFulfillment(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->checkAccess($user, 'supplier', 'purchase_order.update_supplier') !== null
            && $purchaseOrder->supplier_account_id === $user->accountMember?->account_id
            && ! in_array($purchaseOrder->status, ['completed', 'cancelled'], true);
    }
}
