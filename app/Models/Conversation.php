<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Table: conversations — business conversations are between ACCOUNTS.
 *
 * The subject is polymorphic (context_type + context_id), not a set of separate
 * foreign keys. context_type values 'general' and 'support' carry no subject,
 * so context() resolves to null for those.
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'context_type',
        'context_id',
        'title',
        'created_by_account_id',
        'created_by_user_id',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * The RFQ / quotation / listing / purchase order this conversation is about.
     * Resolution relies on the morph map registered in AppServiceProvider.
     */
    public function context(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'context_type', 'context_id');
    }

    public function createdByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /* ── Participants ───────────────────────────────────────────────────── */

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationAccount::class, 'conversation_id');
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'conversation_accounts', 'conversation_id', 'account_id')
            ->withPivot(['participant_capability', 'joined_at', 'left_at', 'is_active'])
            ->withTimestamps();
    }

    public function adminParticipants(): HasMany
    {
        return $this->hasMany(ConversationAdminUser::class, 'conversation_id');
    }

    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_admin_users', 'conversation_id', 'user_id')
            ->withPivot(['joined_at', 'left_at', 'is_active'])
            ->withTimestamps();
    }

    public function userStates(): HasMany
    {
        return $this->hasMany(ConversationUserState::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeForContext(Builder $query, string $contextType, ?int $contextId = null): Builder
    {
        return $query->where('context_type', $contextType)
            ->when($contextId !== null, fn (Builder $q) => $q->where('context_id', $contextId));
    }
}
