<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();
        $account = $user?->activateTeamContext();

        if (! $user || ! $account) {
            return;
        }

        $view->with('user', $user);
        $view->with('account', $account);

        $view->with('unreadNotifications', $user->unreadNotifications()->count());

        $view->with(
            'topbarNotifications',
            $user->unreadNotifications()->latest()->limit(5)->get()
        );

        $view->with('unreadMessages', $this->unreadConversationCount($account, $user));
    }

    private function unreadConversationCount($account, $user): int
    {
        return $account->conversations()
            ->whereNotNull('last_message_at')
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('userStates', fn ($q) => $q->where('user_id', $user->getKey()))
                    ->orWhereHas('userStates', function ($q) use ($user) {
                        $q->where('user_id', $user->getKey())
                            ->whereColumn('last_read_at', '<', 'conversations.last_message_at');
                    });
            })
            ->count();
    }
}
