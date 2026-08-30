<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Messaging is an account-level capability with per-user permission checks.
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
        if ($user->isAdmin()) {
            return true;
        }

        return $this->checkAccess($user, 'messages.view') !== null;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin()) {
            return $conversation->adminParticipants()->where('user_id', $user->id)->where('is_active', true)->exists()
                || $user->hasPermissionTo('platform.conversations.moderate');
        }

        $account = $this->checkAccess($user, 'messages.view');

        return $account !== null && $conversation->accounts()->where('account_id', $account->id)->exists();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin()) {
            return $conversation->adminParticipants()->where('user_id', $user->id)->where('is_active', true)->exists();
        }

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
