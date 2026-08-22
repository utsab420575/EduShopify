<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: messages.
 *
 * Buyer/supplier message: both sender fields set. Admin message: only
 * sender_user_id. System message: both null, with detail in metadata.
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function senderAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('message_type', 'system');
    }

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
}
