<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: subscription_benefits — catalogue of facilities a subscription can
 * grant (featured_supplier, rfq_delay_minutes, max_active_listings, ...).
 *
 * value_type is validated here, not by the database: every stored value
 * (default_value, plan values, override values) goes through
 * normalizeValue() so a boolean benefit always holds true/false, an integer
 * one an int, and so on. Values are resolved per account by
 * App\Services\Billing\BenefitResolver.
 */
class SubscriptionBenefit extends Model
{
    public const VALUE_TYPES = [
        'boolean' => 'Yes / No',
        'integer' => 'Whole number',
        'decimal' => 'Decimal number',
        'text' => 'Text',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'value_type',
        'default_value',
        'unit',
        'sort_order',
        'is_active',
    ];

    protected static function booted(): void
    {
        // Who is "featured" depends on benefit settings (default value, active).
        static::saved(fn () => \App\Services\Billing\FeaturedSupplierService::flush());
        static::deleted(fn () => \App\Services\Billing\FeaturedSupplierService::flush());
    }

    protected function casts(): array
    {
        return [
            'default_value' => 'json',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function planBenefits(): HasMany
    {
        return $this->hasMany(SubscriptionPlanBenefit::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(AccountBenefitOverride::class);
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /* ── Values ─────────────────────────────────────────────────────────── */

    /** Cast a raw form/JSON value to this type (empty → false for boolean, null otherwise). */
    public static function normalizeValue(string $type, mixed $raw): bool|int|float|string|null
    {
        if ($raw === null || $raw === '') {
            return $type === 'boolean' ? false : null;
        }

        return match ($type) {
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $raw,
            'decimal' => round((float) $raw, 4),
            default => (string) $raw,
        };
    }

    /** Validation rules for a value of the given type. */
    public static function valueRules(string $type, bool $required = true): array
    {
        return array_merge($required ? ['required'] : ['nullable'], match ($type) {
            'boolean' => ['boolean'],
            'integer' => ['integer', 'between:-2147483648,2147483647'],
            'decimal' => ['numeric'],
            default => ['string', 'max:1000'],
        });
    }

    public function normalize(mixed $raw): bool|int|float|string|null
    {
        return static::normalizeValue($this->value_type, $raw);
    }

    /** Value used when neither an override, snapshot nor plan defines one. */
    public function fallbackValue(): bool|int|float|string|null
    {
        if ($this->default_value !== null) {
            return $this->normalize($this->default_value);
        }

        return match ($this->value_type) {
            'boolean' => false,
            'integer' => 0,
            'decimal' => 0.0,
            default => null,
        };
    }

    /** Human-readable value, e.g. "Enabled", "25 listings", "1.50 x". */
    public function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($this->value_type) {
            'boolean' => $value ? 'Enabled' : 'Disabled',
            'integer' => trim(number_format((int) $value).' '.$this->unit),
            'decimal' => trim(rtrim(rtrim(number_format((float) $value, 4, '.', ','), '0'), '.').' '.$this->unit),
            default => (string) $value,
        };
    }

    public function typeLabel(): string
    {
        return self::VALUE_TYPES[$this->value_type] ?? ucfirst((string) $this->value_type);
    }

    /** Assigned to a plan or used by an override — such a benefit can only be deactivated. */
    public function isInUse(): bool
    {
        return $this->planBenefits()->exists() || $this->overrides()->exists();
    }
}
