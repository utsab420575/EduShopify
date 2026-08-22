<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Support tickets are a common-account ability — either a buyer or supplier
 * account may open one, gated only by holding an active capability of some kind.
 */
class TicketPolicy
{
    use HandlesAuthorization;

    private function checkAccess(User $user, string $permission): ?Account
    {
        if (! $user->isActive()) {
            return null;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return null;
        }

        if (! $account->hasActiveCapability('buyer') && ! $account->hasActiveCapability('supplier')) {
            return null;
        }

        return $user->hasPermissionTo($permission) ? $account : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkAccess($user, 'tickets.view') !== null;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->checkAccess($user, 'tickets.view') !== null
            && $ticket->account_id === $user->accountMember?->account_id;
    }

    public function create(User $user): bool
    {
        return $this->checkAccess($user, 'tickets.create') !== null;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->checkAccess($user, 'tickets.reply') !== null
            && $ticket->account_id === $user->accountMember?->account_id
            && ! $ticket->isClosed();
    }
}
