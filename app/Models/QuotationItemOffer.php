<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Table: quotation_item_offers.
 *
 * Represents one supplier offer/alternative product under a quotation_item
 * (Product Response layer). Supports marketplace products, custom offers,
 * document uploads, and copied specifications.
 */
class QuotationItemOffer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document')
            ->useDisk(config('media-library.disk_name', 'public'));

        $this->addMediaCollection('gallery')
            ->useDisk(config('media-library.disk_name', 'public'));
    }

    protected $fillable = [
        'quotation_item_id',
        'offer_method',
        'marketplace_product_id',
        'offered_variant_id',
        'product_name',
        'category_id',
        'description',
        'specifications',
        'quantity',
        'unit_id',
        'custom_unit',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount',
        'shipping_charge',
        'total_price',
        'delivery_time',
        'status',
        'is_primary',
        'is_selected',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary'      => 'boolean',
            'is_selected'     => 'boolean',
            'sort_order'      => 'integer',
            'delivery_time'   => 'integer',
            'quantity'        => 'decimal:3',
            'unit_price'      => 'decimal:2',
            'tax_rate'        => 'decimal:4',
            'tax_amount'      => 'decimal:2',
            'discount'        => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'total_price'     => 'decimal:2',
            'specifications'  => 'array',
        ];
    }

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class, 'quotation_item_id');
    }

    public function marketplaceProduct(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'marketplace_product_id');
    }

    public function offeredVariant(): BelongsTo
    {
        return $this->belongsTo(ListingVariant::class, 'offered_variant_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * This offer's own structured category attribute values — every offer
     * under a Product Response gets its own set (not just the primary), see
     * QuotationService::syncOfferAttributeValues().
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(QuotationItemAttributeValue::class, 'quotation_item_offer_id');
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }
}
