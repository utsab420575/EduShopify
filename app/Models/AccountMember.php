<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_members — source of truth for business membership and ownership.
 */
class AccountMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'member_type',
        'is_primary_owner',
        'status',
        'joined_at',
        'removed_at',
        'removed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_owner' => 'boolean',
            'joined_at'        => 'datetime',
            'removed_at'       => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOwners(Builder $query): Builder
    {
        return $query->where('member_type', 'owner');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOwner(): bool
    {
        return $this->member_type === 'owner';
    }
}
