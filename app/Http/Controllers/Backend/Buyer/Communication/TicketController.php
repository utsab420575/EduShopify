<?php

namespace App\Http\Controllers\Backend\Buyer\Communication;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Communication\StoreTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->currentAccount()->tickets()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.buyer.tickets.index', [
            'tickets' => $tickets,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function store(StoreTicketRequest $request, TicketService $service)
    {
        $this->authorize('create', Ticket::class);

        $ticket = $service->create($this->currentAccount(), $this->currentUser(), $request->validated());

        return redirect()->route('buyer.tickets.show', $ticket)->with('success', 'Support ticket created.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['assignedAdmin', 'messages' => fn ($q) => $q->visibleToAccount()->with('senderUser')->oldest()]);

        return view('backend.buyer.tickets.show', ['ticket' => $ticket]);
    }

    public function reply(Request $request, Ticket $ticket, TicketService $service)
    {
        $this->authorize('reply', $ticket);

        $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $service->reply($ticket, $this->currentAccount(), $this->currentUser(), $request->string('message'));

        return redirect()->route('buyer.tickets.show', $ticket);
    }
}
