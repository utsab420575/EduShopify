<?php

namespace App\Http\Controllers\Backend\Supplier\Communication;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->tickets()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $tickets = $query->paginate(10)->withQueryString();

        return view('backend.supplier.tickets.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'tickets' => $tickets,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function store(Request $request, TicketService $service)
    {
        $account = $this->currentAccount();
        $user = $this->currentUser();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = $service->create($account, $user, $validated);

        return redirect()->route('supplier.tickets.show', $ticket)->with('success', 'Support ticket submitted.');
    }

    public function show(Ticket $ticket)
    {
        $account = $this->currentAccount();
        abort_if($ticket->account_id !== $account->id, 403);

        $ticket->load(['assignedAdmin', 'messages' => fn ($q) => $q->visibleToAccount()->with('senderUser')->oldest()]);

        return view('backend.supplier.tickets.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, Ticket $ticket, TicketService $service)
    {
        $account = $this->currentAccount();
        abort_if($ticket->account_id !== $account->id, 403);

        $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $service->reply($ticket, $account, $this->currentUser(), $request->string('message'));

        return redirect()->route('supplier.tickets.show', $ticket);
    }
}
