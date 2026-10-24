<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

/**
 * Table: supplier_profiles — belongs to an account, never directly to a user.
 *
 * Supplier-owned child records key on supplier_account_id, so relations from
 * here join account_id (local) to supplier_account_id (foreign).
 */
class SupplierProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'slug',
        'display_name',
        'legal_name',
        'company_type',
        'contact_person',
        'contact_email',
        'contact_phone',
        'whatsapp',
        'support_email',
        'website',
        'founded_year',
        'employees',
        'logo',
        'banner',
        'banner_original',
        'banner_crop',
        'banner_fit',
        'profile_photo',
        'description',
        'rating',
        'reviews_count',
        'quotation_response_rate',
        'average_response_minutes',
        'profile_completed_at',
        'current_step',
        'max_step_reached',
    ];

    /** Allowed banner_fit values → the CSS object-fit used for the public hero. */
    public const BANNER_FITS = ['cover', 'contain', 'fill'];

    protected function casts(): array
    {
        return [
            'founded_year'             => 'integer',
            'employees'                => 'integer',
            'rating'                   => 'decimal:2',
            'reviews_count'            => 'integer',
            'quotation_response_rate'  => 'decimal:2',
            'average_response_minutes' => 'integer',
            'profile_completed_at'     => 'datetime',
            'banner_crop'              => 'array',
            'current_step'             => 'integer',
            'max_step_reached'         => 'integer',
        ];
    }

    /**
     * The public storefront URL relies on this slug (frontend_workflow.md
     * Part 66). Generate it automatically once a display name exists, so no
     * Supplier profile silently becomes unreachable from the public site.
     */
    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if ($profile->slug || ! $profile->display_name) {
                return;
            }

            $base = Str::slug($profile->display_name) ?: 'supplier';
            $slug = $base;
            $i = 2;

            while (static::where('slug', $slug)->where('id', '!=', $profile->id ?? 0)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }

            $profile->slug = $slug;
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Location delegates: account_locations is the single source of truth
     * for physical locations (Head Office / Primary Location).
     */
    public function getPrimaryLocationAttribute(): ?AccountLocation
    {
        if (! $this->account) {
            return null;
        }

        if ($this->account->relationLoaded('locations')) {
            return $this->account->locations->firstWhere('is_primary', true)
                ?? $this->account->locations->first();
        }

        return $this->account->locations()->where('is_primary', true)->first()
            ?? $this->account->locations()->first();
    }

    public function country(): HasOneThrough
    {
        return $this->hasOneThrough(
            Country::class,
            AccountLocation::class,
            'account_id',
            'id',
            'account_id',
            'country_id'
        )->where('account_locations.is_primary', true);
    }

    public function state(): HasOneThrough
    {
        return $this->hasOneThrough(
            State::class,
            AccountLocation::class,
            'account_id',
            'id',
            'account_id',
            'state_id'
        )->where('account_locations.is_primary', true);
    }

    public function city(): HasOneThrough
    {
        return $this->hasOneThrough(
            City::class,
            AccountLocation::class,
            'account_id',
            'id',
            'account_id',
            'city_id'
        )->where('account_locations.is_primary', true);
    }

    public function getAddressAttribute(): ?string
    {
        return $this->primaryLocation?->address_line_1;
    }

    public function getCountryIdAttribute(): ?int
    {
        return $this->primaryLocation?->country_id;
    }

    public function getStateIdAttribute(): ?int
    {
        return $this->primaryLocation?->state_id;
    }

    public function getCityIdAttribute(): ?int
    {
        return $this->primaryLocation?->city_id;
    }

    /* ── Supplier-owned records, keyed by supplier_account_id ───────────── */

    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_account_id', 'account_id');
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(SupplierServiceArea::class, 'supplier_account_id', 'account_id');
    }

    public function isWorldwideSupplier(): bool
    {
        return $this->serviceAreas()->where('area_level', 'global')->where('is_active', true)->exists();
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(SupplierGallery::class, 'supplier_account_id', 'account_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(SupplierVideo::class, 'supplier_account_id', 'account_id');
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class, 'supplier_account_id', 'account_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'supplier_account_id', 'account_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'supplier_account_id', 'account_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'supplier_account_id', 'account_id')
            ->whereIn('status', ['active', 'trialing'])
            ->latestOfMany();
    }

    public function isComplete(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function isDraft(): bool
    {
        return $this->profile_completed_at === null;
    }
}
