<?php

namespace App\Policies;

use App\Models\User;

class SupplierProfilePolicy
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

    public function view(User $user): bool
    {
        return $this->checkAccess($user) && $user->hasPermissionTo('supplier.profile.view');
    }

    public function update(User $user): bool
    {
        return $this->checkAccess($user) && $user->hasPermissionTo('supplier.profile.update');
    }
}
