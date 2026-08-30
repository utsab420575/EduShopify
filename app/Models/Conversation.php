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
        'direct_key',
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
     * The original/primary context (Listing, Rfq, Quotation, PurchaseOrder).
     */
    public function context(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'context_type', 'context_id');
    }

    /**
     * All associated business contexts (Listing, RFQ, Quotation, PO).
     */
    public function contexts(): HasMany
    {
        return $this->hasMany(ConversationContext::class, 'conversation_id');
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

    public function latestMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function getOtherAccount(int $currentAccountId): ?Account
    {
        if ($this->relationLoaded('accounts')) {
            return $this->accounts->firstWhere('id', '!=', $currentAccountId);
        }

        return $this->accounts()->where('accounts.id', '!=', $currentAccountId)->first();
    }

    public function unreadCountForUser(int $userId): int
    {
        $lastReadAt = $this->userStates()
            ->where('user_id', $userId)
            ->value('last_read_at');

        return $this->messages()
            ->where('sender_user_id', '!=', $userId)
            ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
            ->count();
    }

    public function isMutedBy(int $userId): bool
    {
        $state = $this->relationLoaded('userStates')
            ? $this->userStates->firstWhere('user_id', $userId)
            : $this->userStates()->where('user_id', $userId)->first();

        return $state?->muted_at !== null;
    }

    public function isArchivedBy(int $userId): bool
    {
        $state = $this->relationLoaded('userStates')
            ? $this->userStates->firstWhere('user_id', $userId)
            : $this->userStates()->where('user_id', $userId)->first();

        return $state?->archived_at !== null;
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
