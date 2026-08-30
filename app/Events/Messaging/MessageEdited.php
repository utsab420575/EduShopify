<?php

namespace App\Events\Messaging;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;
    public int $conversationId;
    public string $body;
    public string $editedAt;

    public function __construct(Message $message)
    {
        $this->messageId      = $message->id;
        $this->conversationId = $message->conversation_id;
        $this->body           = $message->body ?? '';
        $this->editedAt       = $message->edited_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A');
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('conversation.'.$this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageEdited';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'      => $this->messageId,
            'conversation_id' => $this->conversationId,
            'body'            => $this->body,
            'edited_at'       => $this->editedAt,
        ];
    }
}
