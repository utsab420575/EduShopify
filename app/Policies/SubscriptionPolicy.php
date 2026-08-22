<?php

namespace App\Policies;

use App\Models\User;

class SubscriptionPolicy
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
        return $this->checkAccess($user) && $user->hasPermissionTo('subscription.view');
    }

    public function select(User $user): bool
    {
        if (! $this->checkAccess($user)) return false;

        $account = $user->account;

        // Plan selection requires ACTIVE supplier capability (Point 75)
        if (! $account?->hasActiveCapability('supplier')) {
            return false;
        }

        return $user->hasPermissionTo('subscription.select');
    }

    public function cancel(User $user): bool
    {
        return $this->checkAccess($user) && $user->hasPermissionTo('subscription.cancel');
    }
}
