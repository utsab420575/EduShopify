<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: rfq_supplier_queue (singular) — drives RFQ visibility and delay.
 *
 * Full RFQ access requires eligibility_status = eligible AND available_at in
 * the past, on top of capability, subscription and permission checks.
 */
class RfqSupplierQueue extends Model
{
    use HasFactory;

    protected $table = 'rfq_supplier_queue';

    protected $fillable = [
        'rfq_id',
        'supplier_account_id',
        'subscription_id',
        'subscription_plan_id',
        'source',
        'delay_minutes',
        'available_at',
        'eligibility_status',
        'eligibility_snapshot',
        'notified_at',
        'seen_at',
        'status',
        'decline_reason',
    ];

    protected function casts(): array
    {
        return [
            'delay_minutes'        => 'integer',
            'available_at'         => 'datetime',
            'eligibility_snapshot' => 'array',
            'notified_at'          => 'datetime',
            'seen_at'              => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('eligibility_status', 'eligible');
    }

    /**
     * Queue rows whose delay has elapsed and which are still eligible.
     */
    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('eligibility_status', 'eligible')
            ->where('available_at', '<=', now());
    }

    public function scopeForSupplierAccount(Builder $query, int $supplierAccountId): Builder
    {
        return $query->where('supplier_account_id', $supplierAccountId);
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isEligible(): bool
    {
        return $this->eligibility_status === 'eligible';
    }

    public function isAvailable(): bool
    {
        return $this->isEligible()
            && $this->available_at !== null
            && ! $this->available_at->isFuture();
    }
}
