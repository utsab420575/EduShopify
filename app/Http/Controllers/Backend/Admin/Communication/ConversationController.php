<?php

namespace App\Http\Controllers\Backend\Admin\Communication;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationAdminUser;
use App\Services\MessagingService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.communication.manage');

        $conversations = Conversation::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with('accounts')
            ->latest('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.communication.conversations.index', [
            'conversations' => $conversations,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('platform.communication.manage');

        $conversation->load(['accounts', 'messages.senderAccount', 'messages.senderUser', 'adminUsers']);

        return view('backend.admin.communication.conversations.show', [
            'conversation' => $conversation,
            'isJoined' => $conversation->adminUsers->contains('id', $this->admin()->id),
        ]);
    }

    public function join(Conversation $conversation)
    {
        $this->authorize('platform.communication.manage');

        ConversationAdminUser::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $this->admin()->id],
            ['joined_at' => now(), 'is_active' => true]
        );

        return back()->with('success', 'Joined the conversation.');
    }

    public function store(Request $request, Conversation $conversation, MessagingService $messaging)
    {
        $this->authorize('platform.communication.manage');

        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $messaging->sendMessage($conversation, null, $this->admin(), $request->input('body'));

        return back()->with('success', 'Message sent.');
    }
}
