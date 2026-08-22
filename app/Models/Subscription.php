<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: subscriptions — owned by the supplier ACCOUNT (supplier_account_id).
 *
 * Supplier capability must be active before a subscription becomes active, and
 * only one active/trialing subscription may exist per supplier account.
 */
class Subscription extends Model
{
    protected $fillable = [
        'supplier_account_id',
        'plan_id',
        'selected_by_user_id',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
        'provider_session_id',
        'plan_name_snapshot',
        'price_snapshot',
        'features_snapshot',
        'status',
        'starts_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'expires_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
    ];

    protected $casts = [
        'price_snapshot'       => 'decimal:2',
        'features_snapshot'    => 'array',
        'starts_at'            => 'datetime',
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'expires_at'           => 'datetime',
        'cancelled_at'         => 'datetime',
        'auto_renew'           => 'boolean',
    ];

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    public function rfqQueueEntries(): HasMany
    {
        return $this->hasMany(RfqSupplierQueue::class, 'subscription_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    /**
     * Subscriptions that currently grant access. Trialing counts as current.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trialing'])
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            });
    }

    public function scopeForSupplierAccount(Builder $query, int $supplierAccountId): Builder
    {
        return $query->where('supplier_account_id', $supplierAccountId);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereIn('status', ['active', 'trialing'])
            ->whereBetween('expires_at', [Carbon::now(), Carbon::now()->addDays($days)]);
    }

    /* ── Status helpers ─────────────────────────────────────────────────── */

    public function isActive(): bool
    {
        if (! in_array($this->status, ['active', 'trialing'], true)) {
            return false;
        }

        return ! ($this->expires_at && $this->expires_at->isPast());
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /* ── Lifecycle ──────────────────────────────────────────────────────── */

    /**
     * Activate the subscription and freeze the plan snapshot onto the row.
     *
     * The V4.3 subscriptions table carries provider_* columns (not stripe_*),
     * and the plan snapshot fields exist so RFQ eligibility can be evaluated
     * without re-reading a plan that may since have changed.
     */
    public function activate(?string $providerSubscriptionId = null, ?string $providerCustomerId = null): void
    {
        $plan      = $this->plan;
        $startsAt  = Carbon::now();
        $expiresAt = $plan?->computeExpiresAt($startsAt);

        $this->update([
            'status'                   => 'active',
            'starts_at'                => $startsAt,
            'current_period_start'     => $startsAt,
            'current_period_end'       => $expiresAt,
            'expires_at'               => $expiresAt,
            'provider_subscription_id' => $providerSubscriptionId ?? $this->provider_subscription_id,
            'provider_customer_id'     => $providerCustomerId ?? $this->provider_customer_id,
            'plan_name_snapshot'       => $plan?->name ?? $this->plan_name_snapshot,
            'price_snapshot'           => $plan?->price ?? $this->price_snapshot,
            'features_snapshot'        => $plan?->features ?? $this->features_snapshot,
        ]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status'              => 'cancelled',
            'cancelled_at'        => Carbon::now(),
            'cancellation_reason' => $reason ?? $this->cancellation_reason,
            'auto_renew'          => false,
        ]);
    }
}
