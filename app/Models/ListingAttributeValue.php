<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: listing_attribute_values — typed specification values for a listing.
 * Exactly one of the value_* columns is normally populated, chosen by the
 * attribute's input_type.
 */
class ListingAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'attribute_id',
        'attribute_value_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_date',
        'value_json',
    ];

    protected function casts(): array
    {
        return [
            'value_number'  => 'decimal:4',
            'value_boolean' => 'boolean',
            'value_date'    => 'date',
            'value_json'    => 'array',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id');
    }

    /**
     * The populated value, whichever typed column holds it.
     */
    public function resolvedValue(): mixed
    {
        return $this->attribute_value_id !== null
            ? $this->attributeValue?->value
            : ($this->value_text
                ?? $this->value_number
                ?? $this->value_boolean
                ?? $this->value_date
                ?? $this->value_json);
    }
}
