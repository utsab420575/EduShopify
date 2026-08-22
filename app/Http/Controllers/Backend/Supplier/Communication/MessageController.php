<?php

namespace App\Http\Controllers\Backend\Supplier\Communication;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\MessagingService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use InteractsWithSupplierAccount;

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

        return view('backend.supplier.messages.index', [
            'account' => $account,
            'user' => $user,
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('view', $conversation);

        $account = $this->currentAccount();
        $user = $this->currentUser();

        $messaging->markRead($conversation, $user);

        $conversation->load(['messages.senderUser', 'accounts' => fn ($q) => $q->where('accounts.id', '!=', $account->id), 'accounts.supplierProfile', 'accounts.buyerProfile']);

        return view('backend.supplier.messages.show', [
            'account' => $account,
            'user' => $user,
            'conversation' => $conversation,
            'otherAccount' => $conversation->accounts->first(),
            'initialMessages' => $this->serializeMessages($conversation->messages, $account->id),
        ]);
    }

    public function store(Request $request, Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('send', $conversation);

        $account = $this->currentAccount();
        $user = $this->currentUser();

        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $messaging->send($conversation, $account, $user, $request->string('body'));

        return redirect()->route('supplier.messages.show', $conversation);
    }

    public function poll(Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('view', $conversation);

        $account = $this->currentAccount();
        $user = $this->currentUser();

        $messaging->markRead($conversation, $user);

        $messages = $conversation->messages()->with('senderUser')->orderBy('created_at')->get();

        return response()->json($this->serializeMessages($messages, $account->id));
    }

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
