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

    protected $fillable = [
        'supplier_account_id',
        'created_by_user_id',
        'listing_type',
        'listing_number',
        'main_category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'pricing_type',
        'sales_mode',
        'base_price',
        'compare_at_price',
        'currency_code',
        'min_order_quantity',
        'unit_id',
        'extra_specs',
        'approval_status',
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
            'approved_at'        => 'datetime',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'published_at'       => 'datetime',
        ];
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
        return $query->where('listing_type', 'product');
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->where('listing_type', 'service');
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isProduct(): bool
    {
        return $this->listing_type === 'product';
    }

    public function isService(): bool
    {
        return $this->listing_type === 'service';
    }

    public function isPublished(): bool
    {
        return $this->approval_status === 'approved'
            && $this->is_active
            && $this->published_at !== null;
    }
}
