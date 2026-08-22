<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Award;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardPolicy
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
        return $this->checkAccess($user, 'buyer', 'award.view') !== null
            || $this->checkAccess($user, 'supplier', 'award.view') !== null;
    }

    public function view(User $user, Award $award): bool
    {
        $accountId = $user->accountMember?->account_id;

        if ($award->buyer_account_id === $accountId) {
            return $this->checkAccess($user, 'buyer', 'award.view') !== null;
        }

        if ($award->supplier_account_id === $accountId) {
            return $this->checkAccess($user, 'supplier', 'award.view') !== null;
        }

        return false;
    }

    public function accept(User $user, Award $award): bool
    {
        return $this->checkAccess($user, 'supplier', 'award.accept') !== null
            && $award->supplier_account_id === $user->accountMember?->account_id
            && $award->isAwaitingResponse()
            && ! $award->responseOverdue();
    }

    public function reject(User $user, Award $award): bool
    {
        return $this->checkAccess($user, 'supplier', 'award.reject') !== null
            && $award->supplier_account_id === $user->accountMember?->account_id
            && $award->isAwaitingResponse()
            && ! $award->responseOverdue();
    }
}
