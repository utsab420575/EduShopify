<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: quotation_item_attribute_values — the supplier's structured
 * offered specification for one quotation item, mirroring
 * rfq_item_attribute_values exactly so it can be compared attribute-by-
 * attribute against the buyer's requested value. Exactly one of the value_*
 * columns is normally populated, chosen by the attribute's input_type.
 * quotation_items.specs remains for free-form extras.
 */
class QuotationItemAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_item_id',
        'attribute_id',
        'attribute_value_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_date',
        'value_json',
        'custom_value',
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

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class, 'quotation_item_id');
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
     * The populated value, whichever typed column holds it. A supplier's
     * "Other" custom value either stands alone (select/color with no
     * predefined pick) or, for multi_select, sits alongside value_json —
     * capped at exactly one custom entry per quotation item attribute.
     */
    public function resolvedValue(): mixed
    {
        if ($this->attribute_value_id !== null) {
            return $this->attributeValue?->value;
        }

        if (is_array($this->value_json) && $this->custom_value !== null && $this->custom_value !== '') {
            return array_merge($this->value_json, [$this->custom_value]);
        }

        return $this->custom_value
            ?? $this->value_text
            ?? $this->value_number
            ?? $this->value_boolean
            ?? $this->value_date
            ?? $this->value_json;
    }

    /**
     * Formatted string representation with unit or human-friendly label.
     */
    public function formattedValue(): string
    {
        if ($this->attribute_value_id !== null) {
            return $this->attributeValue?->value ?? '-';
        }

        if (is_array($this->value_json)) {
            $items = $this->value_json;
            if ($this->custom_value !== null && $this->custom_value !== '') {
                $items[] = $this->custom_value;
            }
            return implode(', ', $items);
        }

        if ($this->custom_value !== null && $this->custom_value !== '') {
            return $this->custom_value;
        }

        if ($this->value_boolean !== null) {
            return $this->value_boolean ? 'Yes' : 'No';
        }

        if ($this->value_number !== null) {
            $num = rtrim(rtrim(number_format((float)$this->value_number, 2), '0'), '.');
            $unit = $this->attribute?->unit?->symbol ?? $this->attribute?->unit?->name;
            return $unit ? "{$num} {$unit}" : $num;
        }

        if ($this->value_date !== null) {
            return is_string($this->value_date) ? $this->value_date : $this->value_date->format('Y-m-d');
        }

        if ($this->value_text !== null) {
            $unit = $this->attribute?->unit?->symbol ?? $this->attribute?->unit?->name;
            return $unit ? "{$this->value_text} {$unit}" : $this->value_text;
        }

        return '-';
    }
}
