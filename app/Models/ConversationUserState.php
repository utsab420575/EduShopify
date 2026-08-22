<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: conversation_user_states — per-user read/mute/archive state.
 * This table is keyed by user only; account participation lives in
 * conversation_accounts.
 */
class ConversationUserState extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
        'muted_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'muted_at'     => 'datetime',
            'archived_at'  => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isMuted(): bool
    {
        return $this->muted_at !== null;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
