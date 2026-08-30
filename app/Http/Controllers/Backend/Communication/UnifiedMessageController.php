<?php

namespace App\Http\Controllers\Backend\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Messaging\EditMessageRequest;
use App\Http\Requests\Messaging\SendMessageRequest;
use App\Http\Requests\Messaging\StartConversationRequest;
use App\Http\Requests\Messaging\UpdatePreferencesRequest;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserMessagingPreference;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UnifiedMessageController extends Controller
{
    public function __construct(
        protected MessagingService $messaging
    ) {}

    /**
     * Display the main messaging interface or return JSON list of conversations.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Conversation::class);

        $user = $request->user();
        $account = $user->isAdmin() ? null : $user->activateTeamContext();
        $filter = $request->query('filter', 'active'); // active, unread, archived
        $search = $request->query('search');

        $query = Conversation::query()
            ->with([
                'accounts' => fn ($q) => $account ? $q->where('accounts.id', '!=', $account->id) : $q,
                'accounts.supplierProfile',
                'accounts.buyerProfile',
                'userStates' => fn ($q) => $q->where('user_id', $user->id),
                'latestMessage.senderUser',
                'contexts.addedBy',
            ]);

        if (! $user->isAdmin()) {
            $query->whereHas('accounts', fn ($q) => $q->where('accounts.id', $account->id));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('accounts', fn ($aq) => $aq->where('display_name', 'like', "%{$search}%"))
                    ->orWhereHas('messages', fn ($mq) => $mq->where('body', 'like', "%{$search}%"));
            });
        }

        // Handle archive/unread filters based on user state
        if ($filter === 'archived') {
            $query->whereHas('userStates', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('archived_at'));
        } elseif ($filter === 'unread') {
            $query->where(function ($q) use ($user) {
                $q->whereDoesntHave('userStates', fn ($sq) => $sq->where('user_id', $user->id))
                    ->orWhereHas('userStates', function ($sq) use ($user) {
                        $sq->where('user_id', $user->id)
                            ->whereColumn('conversations.last_message_at', '>', 'conversation_user_states.last_read_at');
                    });
            })->whereHas('messages', fn ($mq) => $mq->where('sender_user_id', '!=', $user->id));
        } else {
            // Active inbox: exclude archived
            $query->whereDoesntHave('userStates', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('archived_at'));
        }

        $conversations = $query->orderByDesc('last_message_at')->paginate(25);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'conversations' => $conversations->items(),
                'has_more'      => $conversations->hasMorePages(),
                'next_page'     => $conversations->nextPageUrl(),
            ]);
        }

        $userPreferences = UserMessagingPreference::forUser($user);

        // Render appropriate dashboard layout based on user role/capability
        $layout = 'backend.layouts.buyer';
        if ($user->isAdmin()) {
            $layout = 'backend.layouts.admin';
        } elseif ($user->isSupplier()) {
            $layout = 'backend.layouts.supplier';
        }

        return view('backend.communication.messages.index', [
            'conversations'   => $conversations,
            'currentAccount'  => $account,
            'currentUser'     => $user,
            'userPreferences' => $userPreferences,
            'activeFilter'    => $filter,
            'layout'          => $layout,
        ]);
    }

    /**
     * Start or reopen persistent conversation and redirect to conversation view.
     */
    public function start(StartConversationRequest $request)
    {
        $user = $request->user();
        $account = $user->activateTeamContext();
        $targetAccount = Account::findOrFail($request->integer('recipient_account_id'));

        $this->authorize('start', Conversation::class);

        $conversation = $this->messaging->startOrGetDirectConversation(
            $account,
            $user,
            $targetAccount,
            $request->input('context_type'),
            $request->integer('context_id') ?: null
        );

        if ($request->filled('initial_message')) {
            $this->messaging->sendMessage(
                $conversation,
                $account,
                $user,
                $request->string('initial_message')->toString()
            );
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'         => true,
                'conversation_id' => $conversation->id,
                'redirect_url'    => route('messages.show', $conversation->id),
            ]);
        }

        return redirect()->route('messages.show', $conversation->id);
    }

    /**
     * Load conversation data, mark seen, and return paginated messages.
     */
    public function show(Conversation $conversation, Request $request)
    {
        $this->authorize('view', $conversation);

        $user = $request->user();
        $account = $user->isAdmin() ? null : $user->activateTeamContext();

        // Mark Seen
        $this->messaging->markConversationSeen($conversation, $user);

        $conversation->load([
            'accounts' => fn ($q) => $account ? $q->where('accounts.id', '!=', $account->id) : $q,
            'accounts.supplierProfile',
            'accounts.buyerProfile',
            'contexts.addedBy',
            'userStates' => fn ($q) => $q->where('user_id', $user->id),
        ]);

        $messagesQuery = $conversation->messages()
            ->withTrashed()
            ->with(['senderUser', 'senderAccount', 'replyTo.senderUser', 'media', 'receipts'])
            ->orderByDesc('id')
            ->limit(30);

        if ($request->filled('before_id')) {
            $messagesQuery->where('id', '<', $request->integer('before_id'));
        }

        $messages = $messagesQuery->get()->reverse()->values();

        $serializedMessages = $this->serializeMessages($messages, $user->id, $account?->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'conversation' => [
                    'id'              => $conversation->id,
                    'title'           => $conversation->title,
                    'other_account'   => $conversation->accounts->first(),
                    'contexts'        => $conversation->contexts,
                    'is_muted'        => $conversation->isMutedBy($user->id),
                    'is_archived'     => $conversation->isArchivedBy($user->id),
                    'last_message_at' => $conversation->last_message_at?->format('d M Y, h:i A'),
                ],
                'messages'     => $serializedMessages,
                'has_more'     => $messages->count() >= 30,
            ]);
        }

        $userPreferences = UserMessagingPreference::forUser($user);

        $layout = 'backend.layouts.buyer';
        if ($user->isAdmin()) {
            $layout = 'backend.layouts.admin';
        } elseif ($user->isSupplier()) {
            $layout = 'backend.layouts.supplier';
        }

        return view('backend.communication.messages.index', [
            'activeConversation' => $conversation,
            'initialMessages'    => $serializedMessages,
            'currentAccount'     => $account,
            'currentUser'        => $user,
            'userPreferences'    => $userPreferences,
            'layout'             => $layout,
        ]);
    }

    /**
     * Send message inside conversation.
     */
    public function store(SendMessageRequest $request, Conversation $conversation): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('send', $conversation);

        $user = $request->user();
        $account = $user->activateTeamContext();

        $message = $this->messaging->sendMessage(
            $conversation,
            $account,
            $user,
            $request->input('body'),
            $request->file('attachments') ?: [],
            $request->integer('reply_to_message_id') ?: null
        );

        $serialized = $this->serializeSingleMessage($message, $user->id, $account->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $serialized,
            ]);
        }

        if ($user->isSupplier() && \Illuminate\Support\Facades\Route::has('supplier.messages.show')) {
            return redirect()->route('supplier.messages.show', $conversation->id)->with('success', 'Message sent successfully.');
        }

        if (\Illuminate\Support\Facades\Route::has('buyer.messages.show')) {
            return redirect()->route('buyer.messages.show', $conversation->id)->with('success', 'Message sent successfully.');
        }

        return redirect()->route('messages.show', $conversation->id)->with('success', 'Message sent successfully.');
    }

    /**
     * Edit message text.
     */
    public function update(EditMessageRequest $request, Message $message): JsonResponse
    {
        $user = $request->user();

        $this->messaging->editMessage($message, $user, $request->string('body')->toString());

        return response()->json([
            'success'   => true,
            'message'   => $message->fresh(),
            'edited_at' => $message->edited_at?->format('d M Y, h:i A'),
        ]);
    }

    /**
     * Soft delete message.
     */
    public function destroy(Message $message, Request $request): JsonResponse
    {
        $user = $request->user();

        $this->messaging->deleteMessage($message, $user);

        return response()->json([
            'success'    => true,
            'message_id' => $message->id,
        ]);
    }

    /**
     * Acknowledge delivery receipt.
     */
    public function delivered(Message $message, Request $request): JsonResponse
    {
        $user = $request->user();

        $this->messaging->acknowledgeDelivered($message, $user);

        return response()->json(['success' => true]);
    }

    /**
     * Acknowledge conversation seen.
     */
    public function seen(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        $this->messaging->markConversationSeen($conversation, $user);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle conversation mute state for current user.
     */
    public function toggleMute(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorize('view', $conversation);

        $isMuted = $this->messaging->toggleMute($conversation, $request->user());

        return response()->json([
            'success'  => true,
            'is_muted' => $isMuted,
        ]);
    }

    /**
     * Toggle conversation archive state for current user.
     */
    public function toggleArchive(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorize('view', $conversation);

        $isArchived = $this->messaging->toggleArchive($conversation, $request->user());

        return response()->json([
            'success'     => true,
            'is_archived' => $isArchived,
        ]);
    }

    /**
     * Securely download or view private message attachment with conversation access authorization.
     */
    public function downloadAttachment(Message $message, Media $media)
    {
        $conversation = $message->conversation;
        $this->authorize('view', $conversation);

        if ($media->model_type !== Message::class || (int) $media->model_id !== (int) $message->id) {
            abort(404);
        }

        return response()->download($media->getPath(), $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    /**
     * Fetch the current user's messaging preferences (sound, browser notification, email reminders).
     */
    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'preferences' => UserMessagingPreference::forUser($request->user()),
        ]);
    }

    /**
     * Update user messaging preferences (sound, browser notification, email reminders).
     */
    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        $pref = UserMessagingPreference::forUser($request->user());
        $pref->update($request->validated());

        return response()->json([
            'success'     => true,
            'preferences' => $pref,
        ]);
    }

    /* ── Helper Serializers ─────────────────────────────────────────────── */

    private function serializeMessages($messages, int $currentUserId, ?int $currentAccountId): array
    {
        return $messages->map(fn ($m) => $this->serializeSingleMessage($m, $currentUserId, $currentAccountId))->values()->all();
    }

    private function serializeSingleMessage(Message $message, int $currentUserId, ?int $currentAccountId): array
    {
        $attachments = $message->getMedia('attachments')->map(fn ($media) => [
            'id'        => $media->id,
            'name'      => $media->file_name,
            'size'      => $media->human_readable_size,
            'mime_type' => $media->mime_type,
            'url'       => route('messages.attachments.download', [$message->id, $media->id]),
            'is_image'  => str_starts_with($media->mime_type, 'image/'),
        ])->values()->all();

        $isMine = $currentAccountId
            ? $message->sender_account_id === $currentAccountId
            : $message->sender_user_id === $currentUserId;

        return [
            'id'                  => $message->id,
            'conversation_id'     => $message->conversation_id,
            'reply_to_message_id' => $message->reply_to_message_id,
            'reply_to'            => $message->replyTo ? [
                'id'          => $message->replyTo->id,
                'body'        => $message->replyTo->trashed() ? 'This message was deleted' : $message->replyTo->body,
                'sender_name' => $message->replyTo->senderUser?->name ?? 'User',
            ] : null,
            'sender_account_id'   => $message->sender_account_id,
            'sender_user_id'      => $message->sender_user_id,
            'sender_name'         => $message->senderUser?->name ?? 'User',
            'sender_account_name' => $message->senderAccount?->display_name ?? '',
            'message_type'        => $message->message_type,
            'body'                => $message->trashed() ? null : $message->body,
            'is_deleted'          => $message->trashed(),
            'is_edited'           => $message->wasEdited(),
            'edited_at'           => $message->edited_at?->format('d M, h:i A'),
            'attachments'         => $message->trashed() ? [] : $attachments,
            'metadata'            => $message->metadata,
            'is_mine'             => $isMine,
            'is_system'           => $message->isSystem(),
            'is_delivered'        => $message->isDelivered(),
            'is_seen'             => $message->isSeen(),
            'created_at'          => $message->created_at->format('d M Y, h:i A'),
            'created_at_time'     => $message->created_at->format('h:i A'),
            'created_at_iso'      => $message->created_at->toIso8601String(),
        ];
    }
}
