<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: rfq_items — every RFQ must carry at least one.
 */
class RfqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'item_type',
        'listing_id',
        'category_id',
        'item_name',
        'description',
        'quantity',
        'unit_id',
        'custom_unit',
        'estimated_unit_price',
        'specs',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity'             => 'decimal:3',
            'estimated_unit_price' => 'decimal:2',
            'specs'                => 'array',
            'sort_order'           => 'integer',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(RfqItemAttributeValue::class, 'rfq_item_id');
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'rfq_item_id');
    }

    public function quotationRevisionItems(): HasMany
    {
        return $this->hasMany(QuotationRevisionItem::class, 'rfq_item_id');
    }
}
