<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuyerProfilePolicy
{
    use HandlesAuthorization;

    private function checkBuyerAccess(User $user, string $permission): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        if (! $account->hasActiveCapability('buyer')) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    public function view(User $user): bool
    {
        return $this->checkBuyerAccess($user, 'buyer.profile.view');
    }

    public function update(User $user): bool
    {
        return $this->checkBuyerAccess($user, 'buyer.profile.update');
    }
}
