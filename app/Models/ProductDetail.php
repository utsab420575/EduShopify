<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: product_details — product-only fields for a listing.
 */
class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'product_type',
        'stock_status',
        'stock_quantity',
        'weight',
        'weight_unit',
        'lead_time_days',
        'warranty_terms',
        'support_terms',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'decimal:3',
            'weight'         => 'decimal:3',
            'lead_time_days' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function isVariable(): bool
    {
        return $this->product_type === 'variable';
    }

    public function inStock(): bool
    {
        return in_array($this->stock_status, ['in_stock', 'limited'], true);
    }
}
