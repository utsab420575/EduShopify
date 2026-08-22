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
            'attributeValues.attribute',
            'variants' => fn ($q) => $q->active(),
            'variants.unit',
            'variants.tierPrices',
            'tierPrices',
            'supplierAccount.supplierProfile.country',
            'supplierAccount.supplierProfile.city',
        ]);

        $related = PublicListingQuery::base()
            ->where('id', '!=', $listing->id)
            ->where(function ($q) use ($listing) {
                $q->where('main_category_id', $listing->main_category_id)
                    ->orWhere('supplier_account_id', $listing->supplier_account_id);
            })
            ->with(['mainCategory', 'brand', 'supplierAccount.supplierProfile'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('frontend.catalog.show', [
            'listing' => $listing,
            'related' => $related,
        ]);
    }
}
