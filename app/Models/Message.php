<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'reply_to_message_id',
        'sender_account_id',
        'sender_user_id',
        'message_type',
        'body',
        'metadata',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'  => 'array',
            'edited_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk(config('media-library.disk_name', 'local'));
    }

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'message_id');
    }

    public function senderAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('message_type', 'system');
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isSystem(): bool
    {
        return $this->message_type === 'system';
    }

    public function isFromPlatformStaff(): bool
    {
        return $this->sender_user_id !== null && $this->sender_account_id === null;
    }

    public function wasEdited(): bool
    {
        return $this->edited_at !== null;
    }

    /**
     * True if at least one recipient user has received/acknowledged delivery.
     */
    public function isDelivered(): bool
    {
        return $this->receipts()->whereNotNull('delivered_at')->exists();
    }

    /**
     * True if at least one recipient user has opened/seen the message.
     */
    public function isSeen(): bool
    {
        return $this->receipts()->whereNotNull('seen_at')->exists();
    }

    public function isDeliveredTo(int $userId): bool
    {
        return $this->receipts()->where('user_id', $userId)->whereNotNull('delivered_at')->exists();
    }

    public function isSeenBy(int $userId): bool
    {
        return $this->receipts()->where('user_id', $userId)->whereNotNull('seen_at')->exists();
    }
}
