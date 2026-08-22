<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function create(Account $account, User $user, array $data): Ticket
    {
        return DB::transaction(function () use ($account, $user, $data) {
            $ticket = Ticket::create([
                'ticket_number'    => $this->generateTicketNumber(),
                'account_id'       => $account->id,
                'created_by_user_id' => $user->id,
                'related_type'     => $data['related_type'] ?? null,
                'related_id'       => $data['related_id'] ?? null,
                'subject'          => $data['subject'],
                'description'      => $data['description'] ?? null,
                'priority'         => $data['priority'] ?? 'normal',
                'status'           => 'open',
                'last_reply_at'    => now(),
            ]);

            if (! empty($data['description'])) {
                TicketMessage::create([
                    'ticket_id'         => $ticket->id,
                    'sender_account_id' => $account->id,
                    'sender_user_id'    => $user->id,
                    'message'           => $data['description'],
                    'is_internal_note'  => false,
                ]);
            }

            return $ticket;
        });
    }

    public function reply(Ticket $ticket, Account $account, User $user, string $message): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $account, $user, $message) {
            $ticketMessage = TicketMessage::create([
                'ticket_id'         => $ticket->id,
                'sender_account_id' => $account->id,
                'sender_user_id'    => $user->id,
                'message'           => $message,
                'is_internal_note'  => false,
            ]);

            $ticket->update([
                'last_reply_at' => now(),
                'status'        => $ticket->status === 'closed' ? 'open' : 'pending',
                'reopened_at'   => $ticket->status === 'closed' ? now() : $ticket->reopened_at,
            ]);

            return $ticketMessage;
        });
    }

    private function generateTicketNumber(): string
    {
        $year   = date('Y');
        $latest = Ticket::withTrashed()->where('ticket_number', 'like', "TKT-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "TKT-{$year}-{$seq}";
    }
}
