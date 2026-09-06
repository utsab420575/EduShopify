<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;
use App\Services\Catalog\PublicListingQuery;

class ProductController extends Controller
{
    public function show(Listing $listing)
    {
        abort_unless(PublicListingQuery::base()->whereKey($listing->id)->exists(), 404);

        $listing->load([
            'mainCategory',
            'brand',
            'unit',
            'primaryImage',
            'supplierAccount.supplierProfile.country',
            'attributeValues.attribute.unit',
            'attributeValues.attributeValue',
            'allTierPrices',
            'productReviews' => fn ($q) => $q->published()->with('buyerAccount.buyerProfile')->latest('published_at'),
        ]);

        // If cached aggregates on the listing are zero but published reviews exist, auto-sync.
        if ((int) ($listing->product_reviews_count ?? 0) === 0 && $listing->productReviews->isNotEmpty()) {
            $stats = Review::where('listing_id', $listing->id)
                ->product()
                ->published()
                ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
                ->first();

            if ($stats && $stats->cnt > 0) {
                $listing->product_rating = round((float) $stats->avg_rating, 2);
                $listing->product_reviews_count = (int) $stats->cnt;
                $listing->saveQuietly();
            }
        }

        $images = $listing->getMedia('gallery');
        if ($images->isEmpty() && $listing->primaryImage) {
            $images = collect([$listing->primaryImage]);
        }

        $priceMin = $listing->allTierPrices->min('unit_price') ?? $listing->base_price;
        $priceMax = $listing->allTierPrices->max('unit_price') ?? $listing->base_price;

        $supplierProfile = $listing->supplierAccount?->supplierProfile;

        $dealsCount = Review::where('supplier_account_id', $listing->supplier_account_id)
            ->purchaseExperience()
            ->published()
            ->count();

        $yearsActive = $supplierProfile?->founded_year
            ? now()->year - $supplierProfile->founded_year
            : null;

        $relatedProducts = PublicListingQuery::products()
            ->where('main_category_id', $listing->main_category_id)
            ->where('id', '!=', $listing->id)
            ->with(['brand', 'primaryImage'])
            ->limit(4)
            ->get();

        return view('frontend_new.products.show', [
            'listing' => $listing,
            'images' => $images,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'supplierProfile' => $supplierProfile,
            'dealsCount' => $dealsCount,
            'yearsActive' => $yearsActive,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
