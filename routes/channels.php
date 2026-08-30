<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (! $conversation) {
        return false;
    }

    // Check if staff / admin in conversation_admin_users or platform moderator
    if ($user->isAdmin()) {
        $isAdminParticipant = $conversation->adminParticipants()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($isAdminParticipant || $user->hasPermissionTo('platform.conversations.moderate')) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'is_admin'   => true,
                'account_id' => null,
            ];
        }
    }

    $account = $user->activateTeamContext();
    if (! $account || ! $account->isActive()) {
        return false;
    }

    if (! $account->hasActiveCapability('buyer') && ! $account->hasActiveCapability('supplier')) {
        return false;
    }

    if (! $user->hasPermissionTo('messages.view')) {
        return false;
    }

    $isParticipant = $conversation->accounts()
        ->where('accounts.id', $account->id)
        ->wherePivot('is_active', true)
        ->exists();

    if (! $isParticipant) {
        return false;
    }

    return [
        'id'         => $user->id,
        'name'       => $user->name,
        'account_id' => $account->id,
        'capability' => $account->hasActiveCapability('buyer') ? 'buyer' : 'supplier',
    ];
});

Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});
