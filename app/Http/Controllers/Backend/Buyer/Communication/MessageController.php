<?php

namespace App\Http\Controllers\Backend\Buyer\Communication;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\MessagingService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $this->authorize('viewAny', Conversation::class);

        $account = $this->currentAccount();
        $user = $this->currentUser();

        $conversations = $account->conversations()
            ->with(['accounts' => fn ($q) => $q->where('accounts.id', '!=', $account->id), 'accounts.supplierProfile', 'accounts.buyerProfile'])
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->with(['userStates' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('backend.buyer.messages.index', ['conversations' => $conversations, 'account' => $account]);
    }

    public function show(Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('view', $conversation);

        $messaging->markRead($conversation, $this->currentUser());

        $conversation->load(['messages.senderUser', 'accounts' => fn ($q) => $q->where('accounts.id', '!=', $this->currentAccount()->id), 'accounts.supplierProfile', 'accounts.buyerProfile']);

        return view('backend.buyer.messages.show', [
            'conversation' => $conversation,
            'otherAccount' => $conversation->accounts->first(),
            'initialMessages' => $this->serializeMessages($conversation->messages, $this->currentAccount()->id),
        ]);
    }

    public function store(Request $request, Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('send', $conversation);

        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $messaging->send($conversation, $this->currentAccount(), $this->currentUser(), $request->string('body'));

        return redirect()->route('buyer.messages.show', $conversation);
    }

    public function poll(Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('view', $conversation);

        $messaging->markRead($conversation, $this->currentUser());

        $messages = $conversation->messages()->with('senderUser')->orderBy('created_at')->get();

        return response()->json($this->serializeMessages($messages, $this->currentAccount()->id));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Message>  $messages
     * @return array<int, array<string, mixed>>
     */
    private function serializeMessages($messages, int $accountId): array
    {
        return $messages->map(fn ($m) => [
            'id' => $m->id,
            'body' => $m->body,
            'is_mine' => $m->sender_account_id === $accountId,
            'is_system' => $m->isSystem(),
            'sender_name' => $m->senderUser?->name,
            'created_at' => $m->created_at->format('d M, h:i A'),
        ])->values()->all();
    }
}
