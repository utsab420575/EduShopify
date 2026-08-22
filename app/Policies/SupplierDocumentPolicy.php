<?php

namespace App\Policies;

use App\Models\SupplierDocument;
use App\Models\User;

class SupplierDocumentPolicy
{
    private function checkAccess(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        return $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists();
    }

    public function manage(User $user): bool
    {
        return $this->checkAccess($user) && $user->hasPermissionTo('supplier.documents.manage');
    }

    public function verify(User $user, SupplierDocument $document): bool
    {
        if (! $this->checkAccess($user)) return false;
        return $document->supplier_account_id === $user->accountMember?->account_id;
    }
}
