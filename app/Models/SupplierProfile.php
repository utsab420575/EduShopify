<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'country_id',
        'state_id',
        'city_id',
        'address',
        'website',
        'founded_year',
        'employees',
        'logo',
        'banner',
        'profile_photo',
        'description',
        'socials',
        'rating',
        'reviews_count',
        'quotation_response_rate',
        'average_response_minutes',
        'profile_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'socials'                  => 'array',
            'founded_year'             => 'integer',
            'employees'                => 'integer',
            'rating'                   => 'decimal:2',
            'reviews_count'            => 'integer',
            'quotation_response_rate'  => 'decimal:2',
            'average_response_minutes' => 'integer',
            'profile_completed_at'     => 'datetime',
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
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
