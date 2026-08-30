<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Table: listings — one shared table for products and services, owned by the
 * supplier ACCOUNT (supplier_account_id), with type-specific detail tables.
 */
class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    protected static function booted(): void
    {
        static::saving(function (Listing $listing) {
            if (! $listing->listing_type_id) {
                $listing->listing_type_id = ListingType::firstOrCreate(
                    ['code' => 'product'],
                    ['name' => 'Product', 'is_active' => true]
                )->id;
            }
            if (! $listing->pricing_type_id) {
                $listing->pricing_type_id = PricingType::firstOrCreate(
                    ['code' => 'fixed'],
                    ['name' => 'Fixed Catalog Price', 'is_active' => true]
                )->id;
            }
            if (! $listing->sales_mode_id) {
                $listing->sales_mode_id = SalesMode::firstOrCreate(
                    ['code' => 'both'],
                    ['name' => 'Both', 'is_active' => true]
                )->id;
            }
        });
    }

    public function fill(array $attributes)
    {
        if (isset($attributes['listing_type']) && ! isset($attributes['listing_type_id'])) {
            $val = $attributes['listing_type'];
            $attributes['listing_type_id'] = is_numeric($val)
                ? (int)$val
                : ListingType::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst($val), 'is_active' => true]
                )->id;
            unset($attributes['listing_type']);
        }

        if (isset($attributes['pricing_type']) && ! isset($attributes['pricing_type_id'])) {
            $val = $attributes['pricing_type'];
            $attributes['pricing_type_id'] = is_numeric($val)
                ? (int)$val
                : PricingType::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst(str_replace('_', ' ', $val)), 'is_active' => true]
                )->id;
            unset($attributes['pricing_type']);
        }

        if (isset($attributes['sales_mode']) && ! isset($attributes['sales_mode_id'])) {
            $val = $attributes['sales_mode'];
            $attributes['sales_mode_id'] = is_numeric($val)
                ? (int)$val
                : SalesMode::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst(str_replace('_', ' ', $val)), 'is_active' => true]
                )->id;
            unset($attributes['sales_mode']);
        }

        return parent::fill($attributes);
    }

    protected $fillable = [
        'supplier_account_id',
        'created_by_user_id',
        'listing_type_id',
        'listing_number',
        'main_category_id',
        'brand_id',
        'primary_image_media_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'pricing_type_id',
        'sales_mode_id',
        'base_price',
        'compare_at_price',
        'currency_code',
        'min_order_quantity',
        'unit_id',
        'extra_specs',
        'approval_status',
        'setup_step',
        'setup_completed_at',
        'last_autosaved_at',
        'rejection_reason',
        'approved_by_user_id',
        'approved_at',
        'is_active',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price'         => 'decimal:2',
            'compare_at_price'   => 'decimal:2',
            'min_order_quantity' => 'decimal:3',
            'extra_specs'        => 'array',
            'setup_step'         => 'integer',
            'setup_completed_at' => 'datetime',
            'last_autosaved_at'  => 'datetime',
            'approved_at'        => 'datetime',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'published_at'       => 'datetime',
        ];
    }

    public function primaryImage(): BelongsTo
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'primary_image_media_id');
    }

    /* ── Ownership ──────────────────────────────────────────────────────── */

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /* ── Classification ─────────────────────────────────────────────────── */

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /* ── Listing classification lookup relations ─────────────────────────── */

    public function listingType(): BelongsTo
    {
        return $this->belongsTo(ListingType::class, 'listing_type_id');
    }

    public function pricingType(): BelongsTo
    {
        return $this->belongsTo(PricingType::class, 'pricing_type_id');
    }

    public function salesMode(): BelongsTo
    {
        return $this->belongsTo(SalesMode::class, 'sales_mode_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'listing_categories', 'listing_id', 'category_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function listingCategories(): HasMany
    {
        return $this->hasMany(ListingCategory::class, 'listing_id');
    }

    /* ── Type-specific details ──────────────────────────────────────────── */

    public function productDetail(): HasOne
    {
        return $this->hasOne(ProductDetail::class, 'listing_id');
    }

    public function serviceDetail(): HasOne
    {
        return $this->hasOne(ServiceDetail::class, 'listing_id');
    }

    /* ── Specification & pricing ────────────────────────────────────────── */

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ListingAttributeValue::class, 'listing_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ListingVariant::class, 'listing_id');
    }

    public function allTierPrices(): HasMany
    {
        return $this->hasMany(ListingTierPrice::class, 'listing_id');
    }

    public function globalTierPrices(): HasMany
    {
        return $this->hasMany(ListingTierPrice::class, 'listing_id')->whereNull('listing_variant_id');
    }

    public function tierPrices(): HasMany
    {
        return $this->globalTierPrices();
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ListingChangeLog::class, 'listing_id');
    }

    /* ── Downstream references ──────────────────────────────────────────── */

    public function sourcedRfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'source_listing_id');
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'listing_id');
    }

    public function offeredQuotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'offered_listing_id');
    }

    public function contactInquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class, 'listing_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('published_at');
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('code', 'product'));
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('code', 'service'));
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isProduct(): bool
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? ($type->code === 'product') : ($type === 'product');
    }

    public function isService(): bool
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? ($type->code === 'service') : ($type === 'service');
    }

    /**
     * Backward-compatible code accessors so existing code like
     * `$listing->listing_type`, `$listing->pricing_type`, `$listing->sales_mode`
     * continues to return the string code after the enum→FK migration.
     */
    public function getListingTypeAttribute(): ?string
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function getPricingTypeAttribute(): ?string
    {
        $type = $this->getRelationValue('pricingType');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function getSalesModeAttribute(): ?string
    {
        $type = $this->getRelationValue('salesMode');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function setListingTypeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['listing_type_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['listing_type_id'] = ListingType::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst($value), 'is_active' => true]
            )->id;
        }
    }

    public function setPricingTypeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['pricing_type_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['pricing_type_id'] = PricingType::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst(str_replace('_', ' ', $value)), 'is_active' => true]
            )->id;
        }
    }

    public function setSalesModeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['sales_mode_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['sales_mode_id'] = SalesMode::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst(str_replace('_', ' ', $value)), 'is_active' => true]
            )->id;
        }
    }

    public function isPublished(): bool
    {
        return $this->approval_status === 'approved'
            && $this->is_active
            && $this->published_at !== null;
    }
}
