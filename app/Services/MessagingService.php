<?php

namespace App\Services;

use App\Events\Messaging\ConversationRead;
use App\Events\Messaging\MessageDeleted;
use App\Events\Messaging\MessageDelivered;
use App\Events\Messaging\MessageEdited;
use App\Events\Messaging\MessageSeen;
use App\Events\Messaging\MessageSent;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\ConversationAccount;
use App\Models\ConversationContext;
use App\Models\ConversationUserState;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessagingService
{
    /**
     * Reuses the existing persistent direct conversation between two accounts,
     * or starts a new one with deterministic direct_key.
     * Attaches specific business context (listing, rfq, quotation, purchase_order)
     * if provided.
     */
    public function startOrGetDirectConversation(
        Account $fromAccount,
        User $user,
        Account $toAccount,
        ?string $contextType = null,
        ?int $contextId = null
    ): Conversation {
        if ($fromAccount->id === $toAccount->id) {
            throw ValidationException::withMessages([
                'recipient_account_id' => 'Cannot start a conversation with your own account.',
            ]);
        }

        // 1. Check open messaging setting if accounts are unrelated
        $allowUnrelated = (bool) Setting::get('messaging', 'allow_unrelated_messaging', true);
        if (! $allowUnrelated && ! $this->hasExistingBusinessRelationship($fromAccount, $toAccount, $contextType, $contextId)) {
            throw new AuthorizationException('Direct messaging between unrelated accounts is currently restricted by platform policy.');
        }

        $directKey = $this->generateDirectKey($fromAccount->id, $toAccount->id);

        return DB::transaction(function () use ($fromAccount, $user, $toAccount, $contextType, $contextId, $directKey) {
            // Find existing persistent conversation by direct_key
            $conversation = Conversation::where('direct_key', $directKey)->lockForUpdate()->first();

            if (! $conversation) {
                // Check if an existing legacy conversation between the two accounts exists without direct_key
                $legacy = Conversation::whereHas('accounts', fn ($q) => $q->where('account_id', $fromAccount->id))
                    ->whereHas('accounts', fn ($q) => $q->where('account_id', $toAccount->id))
                    ->where('status', 'open')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if ($legacy) {
                    $conversation = $legacy;
                    $conversation->update(['direct_key' => $directKey]);
                } else {
                    $conversation = Conversation::create([
                        'direct_key'            => $directKey,
                        'context_type'          => $contextType ?? 'general',
                        'context_id'            => $contextId,
                        'created_by_account_id' => $fromAccount->id,
                        'created_by_user_id'    => $user->id,
                        'status'                => 'open',
                    ]);

                    ConversationAccount::create([
                        'conversation_id'        => $conversation->id,
                        'account_id'             => $fromAccount->id,
                        'participant_capability' => $fromAccount->hasActiveCapability('buyer') ? 'buyer' : 'supplier',
                        'joined_at'              => now(),
                        'is_active'              => true,
                    ]);

                    ConversationAccount::create([
                        'conversation_id'        => $conversation->id,
                        'account_id'             => $toAccount->id,
                        'participant_capability' => $toAccount->hasActiveCapability('supplier') ? 'supplier' : 'buyer',
                        'joined_at'              => now(),
                        'is_active'              => true,
                    ]);
                }
            }

            // If a specific business context is provided, attach it to conversation_contexts
            if ($contextType && $contextId && ! in_array($contextType, ['general', 'support'], true)) {
                $this->attachContext($conversation, $contextType, $contextId, $user);
            }

            return $conversation;
        });
    }

    /**
     * Backward-compatible wrapper for startOrGetDirectConversation.
     */
    public function startOrGetConversation(
        Account $fromAccount,
        User $user,
        Account $toAccount,
        string $contextType = 'general',
        ?int $contextId = null
    ): Conversation {
        return $this->startOrGetDirectConversation($fromAccount, $user, $toAccount, $contextType, $contextId);
    }

    /**
     * Sends a message, uploads attachments to media library,
     * updates last_message_at, auto-unarchives for recipients,
     * updates sender last_read_at, and broadcasts MessageSent.
     *
     * $senderAccount is null for an Admin sending as a platform participant
     * (see the conversation_admin_users branch in routes/channels.php) rather
     * than on behalf of a Buyer/Supplier account.
     *
     * @param  array<UploadedFile>  $attachments
     */
    public function sendMessage(
        Conversation $conversation,
        ?Account $senderAccount,
        User $senderUser,
        ?string $body,
        array $attachments = [],
        ?int $replyToId = null,
        string $type = 'text'
    ): Message {
        $body = trim((string) $body);

        if ($body === '' && empty($attachments)) {
            throw ValidationException::withMessages([
                'body' => 'Message body or at least one attachment is required.',
            ]);
        }

        // Validate reply belongs to the same conversation
        if ($replyToId) {
            $replyExists = Message::where('id', $replyToId)
                ->where('conversation_id', $conversation->id)
                ->exists();

            if (! $replyExists) {
                throw ValidationException::withMessages([
                    'reply_to_message_id' => 'The message you are replying to does not belong to this conversation.',
                ]);
            }
        }

        [$message, $recipientUserIds] = DB::transaction(function () use ($conversation, $senderAccount, $senderUser, $body, $attachments, $replyToId, $type) {
            // Determine message_type
            $messageType = $type;
            if ($messageType === 'text' && ! empty($attachments)) {
                $allImages = true;
                foreach ($attachments as $file) {
                    if ($file instanceof UploadedFile) {
                        $mime = $file->getMimeType() ?: '';
                        if (! str_starts_with($mime, 'image/')) {
                            $allImages = false;
                            break;
                        }
                    }
                }
                $messageType = $allImages ? 'image' : 'file';
            }

            $message = Message::create([
                'conversation_id'     => $conversation->id,
                'reply_to_message_id' => $replyToId,
                'sender_account_id'   => $senderAccount?->id,
                'sender_user_id'      => $senderUser->id,
                'message_type'        => $messageType,
                'body'                => $body !== '' ? $body : null,
            ]);

            // Save attachments with date-wise path pattern: message/YYYY_MM_DD/
            $dateFolder = 'message/'.now()->format('d_m_Y');
            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeOriginalName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueName = sprintf(
                        '%s_%s_%s_%s.%s',
                        $senderAccount?->id ?? 'admin',
                        $message->id,
                        uniqid(),
                        $safeOriginalName,
                        $extension
                    );

                    $message->addMedia($file)
                        ->usingFileName($uniqueName)
                        ->toMediaCollection('attachments');
                }
            }

            // Update conversation last_message_at
            $conversation->update(['last_message_at' => now()]);

            // Auto-unarchive for recipient users
            ConversationUserState::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $senderUser->id)
                ->update(['archived_at' => null]);

            // Collect active authorized recipient user IDs across other accounts in this conversation
            // (all participant accounts, when the sender is an Admin with no account of their own)
            $recipientUserIds = $this->getRecipientUserIds($conversation, $senderAccount?->id);

            return [$message, $recipientUserIds];
        });

        // Read-state updates and broadcasts run only after the message is safely
        // committed — a slow/unreachable broadcast target must never be able to
        // roll back (and silently discard) a message the sender already sent.
        $this->markConversationSeen($conversation, $senderUser);
        MessageSent::dispatch($message, $recipientUserIds);

        return $message;
    }

    /**
     * Backward-compatible alias for send.
     */
    public function send(Conversation $conversation, Account $senderAccount, User $user, string $body): Message
    {
        return $this->sendMessage($conversation, $senderAccount, $user, $body);
    }

    /**
     * Edits message body.
     */
    public function editMessage(Message $message, User $user, string $newBody): Message
    {
        if ($message->sender_user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to edit this message.');
        }

        if ($message->trashed()) {
            throw ValidationException::withMessages(['body' => 'Cannot edit a deleted message.']);
        }

        $newBody = trim($newBody);
        if ($newBody === '') {
            throw ValidationException::withMessages(['body' => 'Message body cannot be empty.']);
        }

        $message->update([
            'body'      => $newBody,
            'edited_at' => now(),
        ]);

        broadcast(new MessageEdited($message));

        return $message;
    }

    /**
     * Soft-deletes message.
     */
    public function deleteMessage(Message $message, User $user): void
    {
        if ($message->sender_user_id !== $user->id && ! $user->isAdmin()) {
            throw new AuthorizationException('You are not authorized to delete this message.');
        }

        $message->delete();

        broadcast(new MessageDeleted($message));
    }

    /**
     * Records Delivered receipt for a message by a recipient user.
     */
    public function acknowledgeDelivered(Message $message, User $user): void
    {
        if ($message->sender_user_id === $user->id) {
            return;
        }

        MessageReceipt::updateOrCreate(
            ['message_id' => $message->id, 'user_id' => $user->id],
            ['delivered_at' => now()]
        );

        broadcast(new MessageDelivered($message, $user->id));
    }

    /**
     * Marks conversation as Seen for the user, updating user state
     * and recording seen_at receipts for all unread messages.
     */
    public function markConversationSeen(Conversation $conversation, User $user): void
    {
        ConversationUserState::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        // Mark receipts seen for messages sent by others
        $unreadMessages = $conversation->messages()
            ->where('sender_user_id', '!=', $user->id)
            ->whereDoesntHave('receipts', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('seen_at'))
            ->get();

        foreach ($unreadMessages as $msg) {
            $receipt = MessageReceipt::firstOrNew([
                'message_id' => $msg->id,
                'user_id'    => $user->id,
            ]);
            if (! $receipt->delivered_at) {
                $receipt->delivered_at = now();
            }
            $receipt->seen_at = now();
            $receipt->save();
        }

        broadcast(new MessageSeen($conversation, $user->id));
        broadcast(new ConversationRead($conversation->id, $user->id));
    }

    /**
     * Backward-compatible alias for markRead.
     */
    public function markRead(Conversation $conversation, User $user): void
    {
        $this->markConversationSeen($conversation, $user);
    }

    /**
     * Toggles mute for current user on conversation.
     */
    public function toggleMute(Conversation $conversation, User $user): bool
    {
        $state = ConversationUserState::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id]
        );

        $isMuted = $state->muted_at === null;
        $state->update(['muted_at' => $isMuted ? now() : null]);

        return $isMuted;
    }

    /**
     * Toggles archive for current user on conversation.
     */
    public function toggleArchive(Conversation $conversation, User $user): bool
    {
        $state = ConversationUserState::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id]
        );

        $isArchived = $state->archived_at === null;
        $state->update(['archived_at' => $isArchived ? now() : null]);

        return $isArchived;
    }

    /**
     * Attaches a business context to a conversation without duplicates.
     * The (conversation_id, context_type, context_id) unique index is the real
     * guarantee against duplicates under concurrent requests; firstOrCreate()
     * alone only avoids the common case. If two requests race and both reach
     * the insert, the loser's constraint violation is caught here and treated
     * as success — the row it wanted already exists.
     */
    public function attachContext(Conversation $conversation, string $contextType, int $contextId, ?User $user = null): ConversationContext
    {
        try {
            return ConversationContext::firstOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'context_type'    => $contextType,
                    'context_id'      => $contextId,
                ],
                [
                    'added_by_user_id' => $user?->id,
                ]
            );
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return ConversationContext::where([
                'conversation_id' => $conversation->id,
                'context_type'    => $contextType,
                'context_id'      => $contextId,
            ])->firstOrFail();
        }
    }

    /**
     * Generates a deterministic direct conversation key from two account IDs.
     */
    public function generateDirectKey(int $accountIdA, int $accountIdB): string
    {
        $min = min($accountIdA, $accountIdB);
        $max = max($accountIdA, $accountIdB);

        return hash('sha256', $min.':'.$max);
    }

    /**
     * Verifies if two accounts have an active or prior business relationship.
     */
    private function hasExistingBusinessRelationship(Account $from, Account $to, ?string $contextType, ?int $contextId): bool
    {
        // Check if an existing conversation exists between the accounts
        $hasConv = Conversation::whereHas('accounts', fn ($q) => $q->where('account_id', $from->id))
            ->whereHas('accounts', fn ($q) => $q->where('account_id', $to->id))
            ->exists();

        if ($hasConv) {
            return true;
        }

        // Check RFQs
        if ($contextType === 'rfq' && $contextId) {
            return true;
        }

        // Check Quotations
        if ($contextType === 'quotation' && $contextId) {
            return true;
        }

        // Check Purchase Orders
        if ($contextType === 'purchase_order' && $contextId) {
            return true;
        }

        // Check Listings
        if ($contextType === 'listing' && $contextId) {
            return true;
        }

        return false;
    }

    /**
     * Returns all active authorized user IDs belonging to recipient accounts in this conversation.
     * $senderAccountId is null when the sender is an Admin with no account of their own —
     * in that case every participant account is a recipient.
     *
     * @return array<int>
     */
    public function getRecipientUserIds(Conversation $conversation, ?int $senderAccountId): array
    {
        $recipientAccounts = $conversation->accounts()
            ->when($senderAccountId !== null, fn ($q) => $q->where('accounts.id', '!=', $senderAccountId))
            ->wherePivot('is_active', true)
            ->with(['users' => fn ($q) => $q->where('account_members.status', 'active')])
            ->get();

        $userIds = [];
        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();

        try {
            foreach ($recipientAccounts as $account) {
                $registrar->setPermissionsTeamId($account->id);
                foreach ($account->users as $member) {
                    $member->unsetRelation('roles')->unsetRelation('permissions');
                    if ($member->hasPermissionTo('messages.view')) {
                        $userIds[] = $member->id;
                    }
                }
            }
        } finally {
            $registrar->setPermissionsTeamId($originalTeamId);
        }

        return array_values(array_unique($userIds));
    }
}
