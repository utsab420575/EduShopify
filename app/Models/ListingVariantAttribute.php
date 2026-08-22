<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: listing_variant_attributes — one value per variant attribute.
 * custom_value is used only when no predefined attribute value exists.
 */
class ListingVariantAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_variant_id',
        'attribute_id',
        'attribute_value_id',
        'custom_value',
    ];

    public function listingVariant(): BelongsTo
    {
        return $this->belongsTo(ListingVariant::class, 'listing_variant_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id');
    }

    public function resolvedValue(): ?string
    {
        return $this->attribute_value_id !== null
            ? $this->attributeValue?->value
            : $this->custom_value;
    }
}
