<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: listing_variants — variants of a variable product listing.
 */
class ListingVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'listing_id',
        'sku',
        'name',
        'combination_key',
        'price',
        'compare_at_price',
        'currency_code',
        'stock_status',
        'stock_quantity',
        'weight',
        'weight_unit',
        'min_order_quantity',
        'unit_id',
        'lead_time_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'compare_at_price'   => 'decimal:2',
            'stock_quantity'     => 'decimal:3',
            'weight'             => 'decimal:3',
            'min_order_quantity' => 'decimal:3',
            'lead_time_days'     => 'integer',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function mediaAssignments(): HasMany
    {
        return $this->hasMany(ListingVariantMedia::class, 'listing_variant_id')->orderBy('sort_order');
    }

    public function images()
    {
        return $this->belongsToMany(
            \Spatie\MediaLibrary\MediaCollections\Models\Media::class,
            'listing_variant_media',
            'listing_variant_id',
            'media_id'
        )->withPivot(['is_primary', 'sort_order'])->withTimestamps()->orderByPivot('sort_order');
    }

    public function primaryImage()
    {
        return $this->images()->wherePivot('is_primary', true);
    }

    public static function generateCombinationKey(array $attributes): ?string
    {
        if (empty($attributes)) {
            return null;
        }

        $normalized = collect($attributes)
            ->filter(fn ($val) => $val !== null && $val !== '')
            ->map(function ($value, $attributeId) {
                return [
                    'attribute_id' => (int) $attributeId,
                    'value'        => trim((string) $value),
                ];
            })
            ->sortBy('attribute_id')
            ->map(fn ($item) => $item['attribute_id'] . ':' . $item['value'])
            ->implode('|');

        return !empty($normalized) ? sha1($normalized) : null;
    }

    public function variantAttributes(): HasMany
    {
        return $this->hasMany(ListingVariantAttribute::class, 'listing_variant_id');
    }

    public function attributes(): HasMany
    {
        return $this->variantAttributes();
    }

    public function tierPrices(): HasMany
    {
        return $this->hasMany(ListingTierPrice::class, 'listing_variant_id');
    }

    public function offeredQuotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'offered_variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
