<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\ConversationAccount;
use App\Models\ConversationUserState;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /**
     * Reuse an existing open conversation between the same two accounts
     * (scoped by context when given), or start a new one.
     */
    public function startOrGetConversation(
        Account $fromAccount,
        User $user,
        Account $toAccount,
        string $contextType = 'general',
        ?int $contextId = null
    ): Conversation {
        $existing = Conversation::query()
            ->where('context_type', $contextType)
            ->when($contextId !== null, fn ($q) => $q->where('context_id', $contextId))
            ->when($contextType === 'general', fn ($q) => $q->whereNull('context_id'))
            ->where('status', 'open')
            ->whereHas('accounts', fn ($q) => $q->where('account_id', $fromAccount->id))
            ->whereHas('accounts', fn ($q) => $q->where('account_id', $toAccount->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($fromAccount, $user, $toAccount, $contextType, $contextId) {
            $conversation = Conversation::create([
                'context_type'          => $contextType,
                'context_id'            => $contextId,
                'created_by_account_id' => $fromAccount->id,
                'created_by_user_id'    => $user->id,
                'status'                => 'open',
            ]);

            ConversationAccount::create([
                'conversation_id'        => $conversation->id,
                'account_id'             => $fromAccount->id,
                'participant_capability' => 'buyer',
                'joined_at'              => now(),
                'is_active'              => true,
            ]);

            ConversationAccount::create([
                'conversation_id'        => $conversation->id,
                'account_id'             => $toAccount->id,
                'participant_capability' => 'supplier',
                'joined_at'              => now(),
                'is_active'              => true,
            ]);

            return $conversation;
        });
    }

    public function send(Conversation $conversation, Account $senderAccount, User $user, string $body): Message
    {
        return DB::transaction(function () use ($conversation, $senderAccount, $user, $body) {
            $message = Message::create([
                'conversation_id'   => $conversation->id,
                'sender_account_id' => $senderAccount->id,
                'sender_user_id'    => $user->id,
                'message_type'      => 'text',
                'body'              => $body,
            ]);

            $conversation->update(['last_message_at' => now()]);

            $this->markRead($conversation, $user);

            return $message;
        });
    }

    public function markRead(Conversation $conversation, User $user): void
    {
        ConversationUserState::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );
    }
}
