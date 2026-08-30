<?php

namespace App\Services\Catalog;

use App\Models\Attribute;
use App\Models\ListingVariantAttribute;
use Illuminate\Support\Collection;

/**
 * Read-only comparison-matrix builder for the public /compare page. Never
 * writes to listings/attribute-value tables — selection state lives in the
 * client's localStorage; this service only ever re-derives fresh data from
 * the database for whatever (listing_id, variant_id) pairs it's handed.
 *
 * Eligibility is enforced exclusively through PublicListingQuery::base(),
 * the same gate every other public controller uses — an id that doesn't
 * resolve through it (draft/rejected/inactive/deleted/nonexistent) is
 * silently dropped, never distinguished in the response (no leaking which
 * reason it was excluded for).
 */
class ProductComparisonService
{
    /**
     * Normalizes, dedupes, caps, and resolves the raw client payload into
     * real (Listing, ?ListingVariant) pairs.
     *
     * @param  array<int, array{listing_id: mixed, variant_id: mixed}>  $items
     * @return array{pairs: Collection, removed_ids: array<int>}
     */
    public function resolve(array $items): array
    {
        $max = (int) config('comparison.max_items', 5);

        $seen = [];
        $normalized = [];

        foreach ($items as $item) {
            $listingId = (int) ($item['listing_id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $variantId = isset($item['variant_id']) && $item['variant_id'] !== null && $item['variant_id'] !== ''
                ? (int) $item['variant_id']
                : null;

            $key = $listingId.':'.($variantId ?? '0');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $normalized[] = ['listing_id' => $listingId, 'variant_id' => $variantId];

            if (count($normalized) >= $max) {
                break;
            }
        }

        if (empty($normalized)) {
            return ['pairs' => collect(), 'removed_ids' => []];
        }

        $listingIds = collect($normalized)->pluck('listing_id')->unique()->values();

        $listings = PublicListingQuery::base()
            ->whereIn('id', $listingIds)
            ->with([
                'mainCategory',
                'brand',
                'unit',
                'listingType',
                'pricingType',
                'primaryImage',
                'media',
                'supplierAccount.supplierProfile',
                'attributeValues.attribute.attributeGroup',
                'attributeValues.attribute.unit',
                'attributeValues.attributeValue',
                'variants' => fn ($q) => $q->where('is_active', true),
                'variants.unit',
                'variants.variantAttributes.attribute.unit',
                'variants.variantAttributes.attributeValue',
            ])
            ->get()
            ->keyBy('id');

        $removedIds = $listingIds->diff($listings->keys())->values()->all();

        $pairs = collect($normalized)
            ->map(function (array $entry) use ($listings) {
                $listing = $listings->get($entry['listing_id']);
                if (! $listing) {
                    return null;
                }

                $variant = null;
                if ($entry['variant_id']) {
                    // A variant belonging to a DIFFERENT listing, an inactive
                    // one, or a tampered id all fail this lookup — fall back
                    // to the base listing rather than dropping the whole
                    // comparison column, since the listing itself is valid.
                    $variant = $listing->variants->firstWhere('id', $entry['variant_id']);
                }

                return ['listing' => $listing, 'variant' => $variant];
            })
            ->filter()
            ->values();

        return ['pairs' => $pairs, 'removed_ids' => $removedIds];
    }

    /**
     * Per-column header data: image, name, supplier, brand, price (the
     * SELECTED variant's own price/currency/MOQ/unit when one is chosen,
     * never mixed with the base listing's — spec's exactness requirement),
     * and the listing's active variant list for the column's own picker.
     */
    public function buildHeaders(Collection $pairs): array
    {
        return $pairs->map(function (array $entry) {
            $listing = $entry['listing'];
            $variant = $entry['variant'];
            $supplierProfile = $listing->supplierAccount?->supplierProfile;

            $price = $variant?->price ?? $listing->base_price;
            $compareAt = $variant?->compare_at_price ?? $listing->compare_at_price;
            $currency = $variant?->currency_code ?? $listing->currency_code;
            $moq = $variant?->min_order_quantity ?? $listing->min_order_quantity;
            $unit = $variant?->unit ?? $listing->unit;

            // Same fallback order as listing-card.blade.php: prefer the explicit
            // primary image, else the first already-eager-loaded media item —
            // never Spatie's getFirstMediaUrl(), which always issues its own
            // fresh query regardless of what's already been loaded.
            $thumbUrl = null;
            if ($listing->primaryImage) {
                $thumbUrl = $listing->primaryImage->getUrl();
            } elseif ($listing->relationLoaded('media') && $listing->media->isNotEmpty()) {
                $first = $listing->media->where('collection_name', 'gallery')->first() ?? $listing->media->first();
                $thumbUrl = $first?->getUrl();
            }

            return [
                'listing_id' => $listing->id,
                'variant_id' => $variant?->id,
                'slug' => $listing->slug,
                'name' => $listing->name,
                'thumb_url' => $thumbUrl,
                'listing_type' => $listing->listing_type,
                'category' => $listing->mainCategory?->name,
                'brand' => $listing->brand?->name,
                'supplier_id' => $listing->supplier_account_id,
                'supplier_name' => $supplierProfile?->display_name,
                'supplier_slug' => $supplierProfile?->slug,
                'pricing_type' => $listing->pricing_type,
                'price' => $price !== null ? (float) $price : null,
                'compare_at_price' => ($compareAt !== null && $price !== null && (float) $compareAt > (float) $price) ? (float) $compareAt : null,
                'currency_code' => $currency,
                'unit' => $unit?->symbol ?? $unit?->name,
                'moq' => $moq !== null ? rtrim(rtrim(number_format((float) $moq, 2), '0'), '.') : null,
                'variants' => $listing->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'label' => $v->name ?: ($v->sku ?: ('Variant #'.$v->id)),
                    'price' => $v->price !== null ? (float) $v->price : null,
                ])->values(),
            ];
        })->values()->all();
    }

    /**
     * Builds the normalized comparison matrix: a union of every attribute_id
     * seen across the selected columns (never string-matched by name), each
     * carrying one formatted value per column or null (rendered as "Not
     * specified" by the view — never invented here). Split into key_specs
     * (attributes.is_filterable = true, the existing admin-curated
     * "important" signal — no separate flag exists to build a truer one)
     * and additional_groups (everything, grouped by attribute_group with
     * category_attribute/attribute sort_order respected).
     */
    public function buildMatrix(Collection $pairs): array
    {
        $columns = $pairs->map(function (array $entry) {
            $listing = $entry['listing'];
            $variant = $entry['variant'];

            $cells = [];

            foreach ($listing->attributeValues as $value) {
                if (! $value->attribute) {
                    continue;
                }
                $text = $value->formattedValue();
                $cells[$value->attribute_id] = [
                    'attribute' => $value->attribute,
                    'text' => $text === '-' ? null : $text,
                ];
            }

            if ($variant) {
                foreach ($variant->variantAttributes as $va) {
                    if (! $va->attribute) {
                        continue;
                    }
                    $text = $this->formatVariantCell($va);
                    if ($text !== null) {
                        // A variant-level value always wins over the base
                        // listing's value for the same attribute — the
                        // column represents the exact selected variant.
                        $cells[$va->attribute_id] = ['attribute' => $va->attribute, 'text' => $text];
                    }
                }
            }

            return $cells;
        });

        /** @var Collection<int, Attribute> $allAttributes */
        $allAttributes = collect();
        foreach ($columns as $cells) {
            foreach ($cells as $attrId => $cell) {
                if (! $allAttributes->has($attrId)) {
                    $allAttributes->put($attrId, $cell['attribute']);
                }
            }
        }

        $buildRows = function (Collection $attributes) use ($columns) {
            return $attributes->sortBy(fn ($a) => (int) $a->sort_order)->values()->map(fn ($attribute) => [
                'attribute_id' => $attribute->id,
                'name' => $attribute->name,
                'unit' => $attribute->unit?->symbol ?? $attribute->unit?->name,
                'values' => $columns->map(fn ($cells) => $cells[$attribute->id]['text'] ?? null)->values()->all(),
            ])->values();
        };

        $keySpecs = $buildRows($allAttributes->filter(fn (Attribute $a) => (bool) $a->is_filterable));

        $additionalGroups = $allAttributes
            ->groupBy(fn (Attribute $a) => $a->attribute_group_id ?? 0)
            ->map(function (Collection $attrs, $groupId) {
                $group = $groupId ? $attrs->first()->attributeGroup : null;

                return [
                    'group_name' => $group?->name ?? 'General Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attrs' => $attrs,
                ];
            })
            ->sortBy('sort_order')
            ->values()
            ->map(fn ($group) => [
                'group_name' => $group['group_name'],
                'rows' => $buildRows($group['attrs']),
            ])
            ->filter(fn ($group) => $group['rows']->isNotEmpty())
            ->values();

        return [
            'key_specs' => $keySpecs,
            'additional_groups' => $additionalGroups,
        ];
    }

    /**
     * ListingVariantAttribute has no typed value_* columns (only
     * attribute_value_id/custom_value — a strict subset of
     * ListingAttributeValue) and no formattedValue() helper of its own, so
     * it's normalized here to the exact same output shape as
     * ListingAttributeValue::formattedValue() for a consistent matrix.
     */
    private function formatVariantCell(ListingVariantAttribute $va): ?string
    {
        if ($va->attribute_value_id !== null) {
            return $va->attributeValue?->value;
        }

        if ($va->custom_value !== null && $va->custom_value !== '') {
            $unit = $va->attribute?->unit?->symbol ?? $va->attribute?->unit?->name;

            return $unit ? "{$va->custom_value} {$unit}" : $va->custom_value;
        }

        return null;
    }
}
