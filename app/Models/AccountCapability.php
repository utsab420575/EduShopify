<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: account_capabilities — Admin-approved Buyer/Supplier eligibility.
 * This is a business capability, never a Spatie role.
 */
class AccountCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'capability_type_id',
        'status',
        'application_attempts',
        'rejection_reason',
        'revision_reason',
        'suspension_reason',
        'applied_by_user_id',
        'applied_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'capability_type_id'   => 'integer',
            'application_attempts' => 'integer',
            'applied_at'           => 'datetime',
            'reviewed_at'          => 'datetime',
            'activated_at'         => 'datetime',
            'suspended_at'         => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function capabilityType(): BelongsTo
    {
        return $this->belongsTo(CapabilityType::class, 'capability_type_id');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function applicationHistory(): HasMany
    {
        return $this->hasMany(CapabilityApplicationHistory::class, 'account_capability_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCapability(Builder $query, string $capability): Builder
    {
        return $query->whereHas('capabilityType', fn($q) => $q->where('code', $capability));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
