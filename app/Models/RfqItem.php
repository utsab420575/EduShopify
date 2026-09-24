<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Table: rfq_items — every RFQ must carry at least one.
 */
class RfqItem extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk(config('media-library.disk_name', 'public'));
    }

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

    public function isMarketplaceProduct(): bool
    {
        return ! empty($this->listing_id);
    }

    public function isRequirement(): bool
    {
        if ($this->isMarketplaceProduct()) {
            return false;
        }

        $specs = $this->specs;
        if (is_array($specs)) {
            if (!empty($specs['is_requirement']) || ($specs['item_mode'] ?? '') === 'requirement') {
                return true;
            }
            foreach ($specs as $key => $val) {
                if (is_array($val) && ($val['name'] ?? '') === '__is_requirement' && ($val['value'] ?? '') === '1') {
                    return true;
                }
                if ($key === 'is_requirement' && $val) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isCustomProduct(): bool
    {
        return ! $this->isMarketplaceProduct() && ! $this->isRequirement();
    }

    /**
     * Get all resolved attribute values for this RFQ item (merging marketplace listing attributes and custom RFQ item attributes).
     *
     * @return \Illuminate\Support\Collection<int, array{id: int|null, name: string, value: string, group_name: string, group_sort: int, attr_sort: int}>
     */
    public function resolvedAttributes(): \Illuminate\Support\Collection
    {
        $attrs = collect();
        if ($this->listing && $this->listing->relationLoaded('attributeValues')) {
            $attrs = $this->listing->attributeValues->map(function ($v) {
                $val = $v->custom_value ?? $v->value_text;
                if ($val === null && $v->value_number !== null) {
                    $val = rtrim(rtrim((string) $v->value_number, '0'), '.');
                }
                if ($val === null) {
                    $val = $v->attributeValue?->name;
                }

                return [
                    'id'         => $v->attribute_id,
                    'name'       => $v->attribute?->name,
                    'value'      => $val,
                    'group_name' => $v->attribute?->attributeGroup?->name ?: 'Key Features',
                    'group_sort' => $v->attribute?->attributeGroup?->sort_order ?? 999,
                    'attr_sort'  => $v->attribute?->sort_order ?? 999,
                ];
            })->filter(fn ($a) => ! empty($a['name']) && ! empty($a['value']));
        }

        if ($this->relationLoaded('attributeValues') ? $this->attributeValues->isNotEmpty() : $this->attributeValues()->exists()) {
            $customAttrs = $this->attributeValues->map(function ($v) {
                return [
                    'id'         => $v->attribute_id,
                    'name'       => $v->attribute?->name,
                    'value'      => $v->formattedValue(),
                    'group_name' => $v->attribute?->attributeGroup?->name ?: 'Key Features',
                    'group_sort' => $v->attribute?->attributeGroup?->sort_order ?? 999,
                    'attr_sort'  => $v->attribute?->sort_order ?? 999,
                ];
            })->filter(fn ($a) => ! empty($a['name']) && ! empty($a['value']));

            $attrs = $attrs->keyBy('name')->merge($customAttrs->keyBy('name'))->values();
        }

        return $attrs;
    }

    /**
     * Group resolved attribute values by attribute group.
     *
     * @return \Illuminate\Support\Collection<int, array{group_name: string, group_sort: int, attributes: array}>
     */
    public function groupedSpecifications(): \Illuminate\Support\Collection
    {
        $attrs = $this->resolvedAttributes();
        if ($attrs->isEmpty()) {
            return collect();
        }

        return $attrs->groupBy('group_name')->map(function ($groupAttrs, $groupName) {
            return [
                'group_name' => $groupName,
                'group_sort' => $groupAttrs->first()['group_sort'] ?? 999,
                'attributes' => $groupAttrs->sortBy('attr_sort')->values()->all(),
            ];
        })->sortBy('group_sort')->values();
    }
}
