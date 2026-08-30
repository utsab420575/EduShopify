<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: attributes — global specification fields usable by categories,
 * listings and variants.
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_group_id',
        'name',
        'slug',
        'input_type',
        'input_type_id',
        'allow_custom_value',
        'unit_id',
        'placeholder',
        'validation_rules',
        'is_filterable',
        'is_variant',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'attribute_group_id' => 'integer',
            'input_type_id'      => 'integer',
            'allow_custom_value' => 'boolean',
            'validation_rules'   => 'array',
            'is_filterable'      => 'boolean',
            'is_variant'         => 'boolean',
            'is_required'        => 'boolean',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function inputType(): BelongsTo
    {
        return $this->belongsTo(InputType::class, 'input_type_id');
    }

    public function hasOptions(): bool
    {
        if ($this->relationLoaded('inputType') && $this->inputType) {
            return (bool) $this->inputType->has_options;
        }

        return in_array($this->input_type, ['select', 'multi_select', 'color']);
    }

    public function isMultiSelect(): bool
    {
        if ($this->relationLoaded('inputType') && $this->inputType) {
            return (bool) $this->inputType->is_multiple;
        }

        return $this->input_type === 'multi_select';
    }

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function group(): BelongsTo
    {
        return $this->attributeGroup();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute', 'attribute_id', 'category_id')
            ->withPivot(['is_required', 'is_filterable', 'is_variant', 'sort_order'])
            ->withTimestamps();
    }

    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class, 'attribute_id');
    }

    public function listingAttributeValues(): HasMany
    {
        return $this->hasMany(ListingAttributeValue::class, 'attribute_id');
    }

    public function listingVariantAttributes(): HasMany
    {
        return $this->hasMany(ListingVariantAttribute::class, 'attribute_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    public function scopeVariant(Builder $query): Builder
    {
        return $query->where('is_variant', true);
    }
}
