<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Messaging is a common-account ability (spec §4.1 "Common account" group),
 * not buyer- or supplier-exclusive — either side of a conversation authorizes
 * the same way, gated only by holding an active capability of some kind.
 */
class ConversationPolicy
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
        return $this->checkAccess($user, 'messages.view') !== null;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        $account = $this->checkAccess($user, 'messages.view');

        return $account !== null && $conversation->accounts()->where('account_id', $account->id)->exists();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        $account = $this->checkAccess($user, 'messages.send');

        return $account !== null
            && $conversation->status === 'open'
            && $conversation->accounts()->where('account_id', $account->id)->wherePivot('is_active', true)->exists();
    }

    public function start(User $user): bool
    {
        return $this->checkAccess($user, 'messages.send') !== null;
    }
}
