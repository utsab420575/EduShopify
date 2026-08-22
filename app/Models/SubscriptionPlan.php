<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: subscription_plans — Admin-created; a supplier selects one only after
 * Supplier capability approval. A free plan is an ordinary plan with is_free.
 */
class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'billing_type',
        'price',
        'currency_code',
        'stripe_product_id',
        'stripe_price_id',
        'trial_days',
        'bonus_days',
        'max_active_listings',
        'max_products',
        'max_services',
        'max_team_members',
        'max_monthly_quotations',
        'rfq_delay_minutes',
        'has_rfq_notifications',
        'has_analytics',
        'has_verified_badge',
        'has_homepage_placement',
        'has_team_members',
        'is_featured',
        'is_free',
        'requires_supplier_approval',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'                      => 'decimal:2',
        'trial_days'                 => 'integer',
        'bonus_days'                 => 'integer',
        'max_active_listings'        => 'integer',
        'max_products'               => 'integer',
        'max_services'               => 'integer',
        'max_team_members'           => 'integer',
        'max_monthly_quotations'     => 'integer',
        'rfq_delay_minutes'          => 'integer',
        'has_rfq_notifications'      => 'boolean',
        'has_analytics'              => 'boolean',
        'has_verified_badge'         => 'boolean',
        'has_homepage_placement'     => 'boolean',
        'has_team_members'           => 'boolean',
        'is_featured'                => 'boolean',
        'is_free'                    => 'boolean',
        'requires_supplier_approval' => 'boolean',
        'features'                   => 'array',
        'is_active'                  => 'boolean',
        'sort_order'                 => 'integer',
    ];

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function rfqQueueEntries(): HasMany
    {
        return $this->hasMany(RfqSupplierQueue::class, 'subscription_plan_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOfBillingType(Builder $query, string $billingType): Builder
    {
        return $query->where('billing_type', $billingType);
    }

    /* ── Billing-type helpers ───────────────────────────────────────────── */

    public function isFree(): bool
    {
        return (bool) $this->is_free || $this->billing_type === 'free';
    }

    public function isMonthly(): bool
    {
        return $this->billing_type === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_type === 'yearly';
    }

    /* ── Duration helpers ───────────────────────────────────────────────── */

    /**
     * Total free access days: trial + bonus for a free plan, bonus only for paid.
     */
    public function totalFreeDays(): int
    {
        return $this->isFree()
            ? ((int) $this->trial_days + (int) $this->bonus_days)
            : (int) $this->bonus_days;
    }

    /**
     * Expiry for a subscription starting at the given moment.
     * monthly → +1 month, yearly → +1 year, free → +trial_days; bonus_days on top.
     *
     * Returns null when the plan never expires. A free plan with no trial and no
     * bonus days is open-ended access, not access that lapses immediately —
     * returning $startsAt there would expire the subscription on activation.
     */
    public function computeExpiresAt(CarbonInterface $startsAt): ?CarbonInterface
    {
        $trialDays = (int) $this->trial_days;
        $bonusDays = (int) $this->bonus_days;

        if ($this->isFree() && $trialDays === 0 && $bonusDays === 0) {
            return null;
        }

        $date = match ($this->billing_type) {
            'free'    => $startsAt->copy()->addDays($trialDays),
            'monthly' => $startsAt->copy()->addMonth(),
            'yearly'  => $startsAt->copy()->addYear(),
            default   => $startsAt->copy()->addMonth(),
        };

        return $bonusDays > 0 ? $date->addDays($bonusDays) : $date;
    }

    /* ── Display helpers ────────────────────────────────────────────────── */

    /**
     * Coarse tier label derived from the plan's own columns. Useful for badges
     * and grouping; it is presentation only and stores nothing.
     */
    public function tierLabel(): string
    {
        if ($this->isFree()) {
            return 'free';
        }

        $slug = strtolower((string) $this->slug);

        return match (true) {
            str_contains($slug, 'basic')                                          => 'basic',
            str_contains($slug, 'standard'), str_contains($slug, 'professional')  => 'professional',
            str_contains($slug, 'featured'), str_contains($slug, 'premium')       => 'featured',
            default                                                               => 'basic',
        };
    }

    public function effectiveCurrencyCode(): string
    {
        return $this->currency_code
            ?? Currency::where('is_default', true)->value('code')
            ?? 'USD';
    }

    public function effectiveCurrencySymbol(): string
    {
        return Currency::where('code', $this->effectiveCurrencyCode())->value('symbol') ?? '$';
    }

    public function formattedPrice(): string
    {
        if ($this->isFree() || (float) $this->price === 0.0) {
            return 'Free';
        }

        return $this->effectiveCurrencySymbol() . number_format((float) $this->price, 2);
    }
}
