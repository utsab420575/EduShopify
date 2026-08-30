<?php

namespace App\Events\Messaging;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $messageData;
    public int $conversationId;
    public ?int $senderAccountId;
    public ?int $senderUserId;
    public array $recipientUserIds;

    /**
     * @param  array<int>  $recipientUserIds
     */
    public function __construct(Message $message, array $recipientUserIds = [])
    {
        $this->conversationId   = $message->conversation_id;
        $this->senderAccountId  = $message->sender_account_id;
        $this->senderUserId     = $message->sender_user_id;
        $this->recipientUserIds = $recipientUserIds;

        $attachments = $message->getMedia('attachments')->map(fn ($media) => [
            'id'        => $media->id,
            'name'      => $media->file_name,
            'size'      => $media->human_readable_size,
            'mime_type' => $media->mime_type,
            'url'       => route('messages.attachments.download', [$message->id, $media->id]),
            'is_image'  => str_starts_with($media->mime_type, 'image/'),
        ])->values()->all();

        $this->messageData = [
            'id'                  => $message->id,
            'conversation_id'     => $message->conversation_id,
            'reply_to_message_id' => $message->reply_to_message_id,
            'reply_to'            => $message->replyTo ? [
                'id'          => $message->replyTo->id,
                'body'        => $message->replyTo->body,
                'sender_name' => $message->replyTo->senderUser?->name ?? 'User',
            ] : null,
            'sender_account_id'   => $message->sender_account_id,
            'sender_user_id'      => $message->sender_user_id,
            'sender_name'         => $message->senderUser?->name ?? 'User',
            'sender_account_name' => $message->senderAccount?->display_name ?? '',
            'message_type'        => $message->message_type,
            'body'                => $message->body,
            'attachments'         => $attachments,
            'metadata'            => $message->metadata,
            'is_system'           => $message->isSystem(),
            'created_at'          => $message->created_at->format('d M Y, h:i A'),
            'created_at_iso'      => $message->created_at->toIso8601String(),
        ];
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PresenceChannel('conversation.'.$this->conversationId),
        ];

        foreach ($this->recipientUserIds as $userId) {
            $channels[] = new PrivateChannel('user.'.$userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'message'             => $this->messageData,
            'conversation_id'     => $this->conversationId,
            'sender_account_name' => $this->messageData['sender_account_name'],
            'sender_name'         => $this->messageData['sender_name'],
            'body_preview'        => \Illuminate\Support\Str::limit($this->messageData['body'] ?: 'Sent an attachment', 80),
        ];
    }
}
