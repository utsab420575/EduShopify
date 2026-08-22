<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

/**
 * Staff-side of the ticket system — TicketService (app/Services/TicketService.php)
 * covers the buyer/supplier side (create + reply). Staff replies always have
 * sender_account_id = null (spec: "sender_account_id is null for platform staff").
 */
class TicketAdminService
{
    public function reply(Ticket $ticket, User $admin, string $message, bool $isInternalNote = false): TicketMessage
    {
        $ticketMessage = TicketMessage::create([
            'ticket_id'         => $ticket->id,
            'sender_account_id' => null,
            'sender_user_id'    => $admin->id,
            'message'           => $message,
            'is_internal_note'  => $isInternalNote,
        ]);

        if (! $isInternalNote) {
            $ticket->update([
                'status'        => 'answered',
                'last_reply_at' => now(),
            ]);
        }

        return $ticketMessage;
    }

    public function assign(Ticket $ticket, User $admin): Ticket
    {
        $ticket->update(['assigned_admin_user_id' => $admin->id]);

        return $ticket->fresh();
    }

    public function resolve(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => 'resolved']);

        return $ticket->fresh();
    }

    public function close(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return $ticket->fresh();
    }

    public function reopen(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => 'open', 'reopened_at' => now(), 'closed_at' => null]);

        return $ticket->fresh();
    }
}
