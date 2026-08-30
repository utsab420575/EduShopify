<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Catalog\PublicListingQuery;

class ListingController extends Controller
{
    public function show(Listing $listing)
    {
        // Route model binding resolves by slug alone; re-check full public
        // eligibility here so a draft/pending/rejected listing safely 404s
        // instead of leaking its existence (frontend_workflow.md Part 73).
        abort_unless(PublicListingQuery::base()->whereKey($listing->id)->exists(), 404);

        $listing->load([
            'mainCategory',
            'brand',
            'unit',
            'categories',
            'productDetail',
            'serviceDetail',
            'media',
            'attributeValues.attribute.attributeGroup',
            'attributeValues.attribute.unit',
            'attributeValues.attributeValue',
            'variants' => fn ($q) => $q->active(),
            'variants.unit',
            'variants.tierPrices',
            'tierPrices',
            'supplierAccount.supplierProfile.country',
            'supplierAccount.supplierProfile.city',
        ]);

        $groupedSpecifications = $listing->attributeValues
            ->groupBy(fn ($val) => $val->attribute?->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attribute?->attributeGroup : null;
                return [
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'items'      => $items->sortBy([
                        ['attribute.sort_order', 'asc'],
                        ['attribute.name', 'asc'],
                    ]),
                ];
            })
            ->sortBy('sort_order')
            ->values();

        $related = PublicListingQuery::base()
            ->where('id', '!=', $listing->id)
            ->where(function ($q) use ($listing) {
                $q->where('main_category_id', $listing->main_category_id)
                    ->orWhere('supplier_account_id', $listing->supplier_account_id);
            })
            ->with(['mainCategory', 'brand', 'unit', 'primaryImage', 'media', 'productDetail', 'serviceDetail', 'supplierAccount.supplierProfile'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('frontend.catalog.show', [
            'listing'               => $listing,
            'groupedSpecifications' => $groupedSpecifications,
            'related'               => $related,
        ]);
    }
}
