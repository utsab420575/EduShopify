<?php

namespace App\Events\Messaging;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDelivered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;
    public int $conversationId;
    public int $userId;
    public string $deliveredAt;

    public function __construct(Message $message, int $userId)
    {
        $this->messageId      = $message->id;
        $this->conversationId = $message->conversation_id;
        $this->userId         = $userId;
        $this->deliveredAt    = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('conversation.'.$this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageDelivered';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'      => $this->messageId,
            'conversation_id' => $this->conversationId,
            'user_id'         => $this->userId,
            'delivered_at'    => $this->deliveredAt,
        ];
    }
}
