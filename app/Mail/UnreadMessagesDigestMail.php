<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadMessagesDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{conversation_id: int, sender_name: string, sender_account: string, unread_count: int, latest_message: string, latest_time: string, url: string}>  $digestItems
     */
    public function __construct(
        public User $recipientUser,
        public array $digestItems,
        public int $totalUnreadCount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('You have %d unread message%s on %s', $this->totalUnreadCount, $this->totalUnreadCount > 1 ? 's' : '', config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.messages.unread-digest',
        );
    }
}
