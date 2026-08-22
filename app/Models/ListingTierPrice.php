<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: listing_tier_prices — quantity-break pricing for a listing or variant.
 */
class ListingTierPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'listing_variant_id',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'currency_code',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'decimal:3',
            'max_quantity' => 'decimal:3',
            'unit_price'   => 'decimal:2',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function listingVariant(): BelongsTo
    {
        return $this->belongsTo(ListingVariant::class, 'listing_variant_id');
    }
}
