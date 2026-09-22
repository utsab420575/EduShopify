<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Table: quotation_revision_item_offers — a frozen copy of one
 * quotation_item_offers row (including its media), written once when a
 * revision is snapshotted and never updated afterward. Mirrors
 * QuotationItemOffer's own collections so a later edit/removal of the live
 * offer never affects historical revision data.
 */
class QuotationRevisionItemOffer extends Model implements HasMedia
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
        'quotation_revision_item_id',
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
        'total_price',
        'delivery_time',
        'status',
        'is_primary',
        'is_selected',
        'sort_order',
        'source_offer_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary'     => 'boolean',
            'is_selected'    => 'boolean',
            'sort_order'     => 'integer',
            'delivery_time'  => 'integer',
            'quantity'       => 'decimal:3',
            'unit_price'     => 'decimal:2',
            'tax_rate'       => 'decimal:4',
            'tax_amount'     => 'decimal:2',
            'discount'       => 'decimal:2',
            'total_price'    => 'decimal:2',
            'specifications' => 'array',
        ];
    }

    public function quotationRevisionItem(): BelongsTo
    {
        return $this->belongsTo(QuotationRevisionItem::class, 'quotation_revision_item_id');
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

    public function sourceOffer(): BelongsTo
    {
        return $this->belongsTo(QuotationItemOffer::class, 'source_offer_id');
    }
}
