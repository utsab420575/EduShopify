<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_benefit_overrides — an admin-set benefit value for one
 * account. While effective it REPLACES the plan/snapshot value (false and 0
 * included); once expired it is simply ignored and the subscription value
 * applies again. Rows are kept as history:
 *   inactive  — switched off by an admin (can be switched back on)
 *   scheduled — starts in the future
 *   active    — in effect now
 *   expired   — past expires_at (revoking sets expires_at to now)
 */
class AccountBenefitOverride extends Model
{
    protected $fillable = [
        'account_id',
        'subscription_benefit_id',
        'value',
        'starts_at',
        'expires_at',
        'is_active',
        'created_by_user_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(SubscriptionBenefit::class, 'subscription_benefit_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** Overrides in effect at $at (default now): active, started, not expired. */
    public function scopeEffective(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $at));
    }

    public function status(?CarbonInterface $at = null): string
    {
        $at ??= now();

        return match (true) {
            ! $this->is_active => 'inactive',
            $this->expires_at !== null && $this->expires_at->lte($at) => 'expired',
            $this->starts_at !== null && $this->starts_at->gt($at) => 'scheduled',
            default => 'active',
        };
    }

    public function isEffective(?CarbonInterface $at = null): bool
    {
        return $this->status($at) === 'active';
    }
}
