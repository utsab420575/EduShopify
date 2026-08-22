<?php

namespace App\Http\Controllers\Backend\Admin\Support;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketAdminService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.tickets.manage');

        $tickets = Ticket::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('search'), fn ($q) => $q->where('subject', 'like', '%'.$request->string('search').'%'))
            ->with(['account', 'assignedAdmin'])
            ->latest('last_reply_at')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.tickets.index', [
            'tickets' => $tickets,
            'status' => $request->string('status')->toString(),
            'priority' => $request->string('priority')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('platform.tickets.manage');

        $ticket->load(['account', 'createdBy', 'assignedAdmin', 'messages.senderUser', 'messages.senderAccount']);

        return view('backend.admin.tickets.show', ['ticket' => $ticket]);
    }

    public function assign(Ticket $ticket, TicketAdminService $service)
    {
        $this->authorize('platform.tickets.manage');

        $service->assign($ticket, $this->admin());

        return back()->with('success', 'Ticket assigned to you.');
    }

    public function reply(Request $request, Ticket $ticket, TicketAdminService $service)
    {
        $this->authorize('platform.tickets.manage');

        $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $service->reply($ticket, $this->admin(), $request->string('message'), $request->boolean('is_internal_note'));

        return back()->with('success', 'Reply sent.');
    }

    public function resolve(Ticket $ticket, TicketAdminService $service)
    {
        $this->authorize('platform.tickets.manage');

        $service->resolve($ticket);

        return back()->with('success', 'Ticket resolved.');
    }

    public function close(Ticket $ticket, TicketAdminService $service)
    {
        $this->authorize('platform.tickets.manage');

        $service->close($ticket);

        return back()->with('success', 'Ticket closed.');
    }

    public function reopen(Ticket $ticket, TicketAdminService $service)
    {
        $this->authorize('platform.tickets.manage');

        $service->reopen($ticket);

        return back()->with('success', 'Ticket reopened.');
    }
}
