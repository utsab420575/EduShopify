<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;
use App\Models\SavedItem;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\Request;

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
            ->with(['brand', 'primaryImage', 'supplierAccount.supplierProfile', 'mainCategory', 'unit'])
            ->limit(4)
            ->get();

        $this->attachSingleListingSavesData($listing);
        $this->attachSavesData($relatedProducts);

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

    /**
     * Toggle saving/favoriting a product (listing) for the authenticated user/account.
     */
    public function toggleSave(Request $request, Listing $listing)
    {
        $user = $request->user();
        if (! $user) {
            session()->forget('frontend_intent');
            $returnUrl = url()->previous() ?: route('v2.products.show', $listing->slug);
            session(['url.intended' => $returnUrl]);

            return response()->json([
                'success'       => false,
                'authenticated' => false,
                'message'       => 'Please sign in to save products to your favorites.',
                'redirect'      => route('login', ['redirect' => $returnUrl]),
            ], 401);
        }

        $account = $user->activateTeamContext()
            ?? $user->accountMember?->account
            ?? $user->account
            ?? \App\Models\Account::where('is_system_account', true)->first()
            ?? \App\Models\Account::first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for your profile.',
            ], 403);
        }

        $existing = SavedItem::where('account_id', $account->id)
            ->where('item_type', 'listing')
            ->where('item_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
            $message = 'Product removed from saved items.';
        } else {
            SavedItem::create([
                'account_id'       => $account->id,
                'saved_by_user_id' => $user->id,
                'visibility'       => 'account',
                'item_type'        => 'listing',
                'item_id'          => $listing->id,
            ]);
            $saved = true;
            $message = 'Product saved to your favorites!';
        }

        $savesCount = SavedItem::where('item_type', 'listing')
            ->where('item_id', $listing->id)
            ->count();

        return response()->json([
            'success'     => true,
            'saved'       => $saved,
            'saves_count' => $savesCount,
            'message'     => $message,
        ]);
    }

    /**
     * Batch attach saves_count and is_saved to a collection of listings.
     */
    public function attachSavesData($listings)
    {
        $items = $listings instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $listings->items()
            : $listings;

        $listingIds = collect($items)->pluck('id')->filter()->all();
        if (empty($listingIds)) {
            return $listings;
        }

        $savesCounts = SavedItem::where('item_type', 'listing')
            ->whereIn('item_id', $listingIds)
            ->selectRaw('item_id, count(*) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->all();

        $currentUserSavedIds = [];
        if (auth()->check()) {
            $user = auth()->user();
            $currentAccount = $user->activateTeamContext()
                ?? $user->accountMember?->account
                ?? $user->account;
            if ($currentAccount) {
                $currentUserSavedIds = SavedItem::where('account_id', $currentAccount->id)
                    ->where('item_type', 'listing')
                    ->whereIn('item_id', $listingIds)
                    ->pluck('item_id')
                    ->all();
            }
        }

        foreach ($items as $listing) {
            $listing->saves_count = (int) ($savesCounts[$listing->id] ?? 0);
            $listing->is_saved = in_array($listing->id, $currentUserSavedIds, true);
        }

        return $listings;
    }

    /**
     * Attach saves_count and is_saved to a single listing.
     */
    public function attachSingleListingSavesData(Listing $listing): Listing
    {
        $listing->saves_count = (int) SavedItem::where('item_type', 'listing')
            ->where('item_id', $listing->id)
            ->count();

        $listing->is_saved = false;
        if (auth()->check()) {
            $user = auth()->user();
            $currentAccount = $user->activateTeamContext()
                ?? $user->accountMember?->account
                ?? $user->account;
            if ($currentAccount) {
                $listing->is_saved = SavedItem::where('account_id', $currentAccount->id)
                    ->where('item_type', 'listing')
                    ->where('item_id', $listing->id)
                    ->exists();
            }
        }

        return $listing;
    }
}
