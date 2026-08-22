<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: quotation_items.
 *
 * rfq_item_id is nullable so a supplier may propose extra items; is_alternative
 * marks an alternative offer against the same RFQ line.
 */
class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'rfq_item_id',
        'offered_listing_id',
        'offered_variant_id',
        'is_alternative',
        'item_name',
        'description',
        'quantity',
        'unit_id',
        'custom_unit',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'line_total',
        'lead_time_days',
        'specs',
    ];

    protected function casts(): array
    {
        return [
            'is_alternative'  => 'boolean',
            'quantity'        => 'decimal:3',
            'unit_price'      => 'decimal:2',
            'tax_rate'        => 'decimal:4',
            'tax_amount'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total'      => 'decimal:2',
            'lead_time_days'  => 'integer',
            'specs'           => 'array',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class, 'rfq_item_id');
    }

    public function offeredListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'offered_listing_id');
    }

    public function offeredVariant(): BelongsTo
    {
        return $this->belongsTo(ListingVariant::class, 'offered_variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'quotation_item_id');
    }

    public function scopeAlternatives(Builder $query): Builder
    {
        return $query->where('is_alternative', true);
    }
}
