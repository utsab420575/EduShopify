<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: role_requests [V4.3]
 * An organization asks Admin to approve a new account-specific Spatie role.
 */
class RoleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'requested_by_user_id',
        'role_name',
        'display_name',
        'capability_scope',
        'requested_permissions',
        'description',
        'status',
        'reviewed_by_user_id',
        'review_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_permissions' => 'array',
            'reviewed_at'           => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
