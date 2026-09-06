<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SavedItem;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->input('search', '');
        $categorySlug = $request->input('category', 'all');

        // All root categories — count listings across full subtree (root + all descendants).
        // Listings are typically assigned to child/leaf categories, not root categories directly,
        // so we must walk the full tree to get accurate counts for each root tab.
        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $category) {
                $ids = array_merge([$category->id], $category->descendantIds());

                $category->listing_count = PublicListingQuery::products()
                    ->where(function (Builder $q) use ($ids) {
                        $q->whereIn('main_category_id', $ids)
                          ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                    })
                    ->count();

                return $category;
            });

        // Build product query
        $listingQuery = PublicListingQuery::products()
            ->with([
                'mainCategory',
                'supplierAccount.supplierProfile',
                'brand',
                'primaryImage',
                'unit',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        // Filter by category tab — match root AND all its descendant categories
        if ($categorySlug && $categorySlug !== 'all') {
            $cat = Category::where('slug', $categorySlug)->first();
            if ($cat) {
                $ids = array_merge([$cat->id], $cat->descendantIds());
                $listingQuery->where(function (Builder $q) use ($ids) {
                    $q->whereIn('main_category_id', $ids)
                      ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                });
            }
        }

        // Live search filter
        if ($search) {
            $listingQuery->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('mainCategory', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $listings = $listingQuery->paginate(12)->withQueryString();

        $listingIds = $listings->pluck('id')->filter()->all();
        $savesCounts = SavedItem::where('item_type', 'listing')
            ->whereIn('item_id', $listingIds)
            ->selectRaw('item_id, count(*) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->all();

        $currentUserSavedListingIds = [];
        if (auth()->check()) {
            $user = auth()->user();
            $currentAccount = $user->activateTeamContext()
                ?? $user->accountMember?->account
                ?? $user->account;
            if ($currentAccount) {
                $currentUserSavedListingIds = SavedItem::where('account_id', $currentAccount->id)
                    ->where('item_type', 'listing')
                    ->whereIn('item_id', $listingIds)
                    ->pluck('item_id')
                    ->all();
            }
        }

        foreach ($listings as $listing) {
            $listing->saves_count = (int) ($savesCounts[$listing->id] ?? 0);
            $listing->is_saved = in_array($listing->id, $currentUserSavedListingIds, true);
        }

        // AJAX live-search & tab filtering → return JSON
        if ($request->expectsJson() || $request->ajax()) {
            $activeCategoryName = 'All Categories';
            if ($categorySlug && $categorySlug !== 'all') {
                $matched = $categories->firstWhere('slug', $categorySlug);
                $activeCategoryName = $matched ? $matched->name : (Category::where('slug', $categorySlug)->value('name') ?? 'Category');
            }

            return response()->json([
                'total'                => $listings->total(),
                'active_category'      => $categorySlug,
                'active_category_name' => $activeCategoryName,
                'search'               => $search,
                'products'             => $listings->map(function ($listing) use ($savesCounts, $currentUserSavedListingIds) {
                    $img = $listing->primaryImage?->getUrl()
                        ?? $listing->getFirstMediaUrl('gallery')
                        ?? null;
                    $supplierProfile = $listing->supplierAccount?->supplierProfile;
                    return [
                        'id'          => $listing->id,
                        'name'        => $listing->name,
                        'slug'        => $listing->slug,
                        'image'       => $img,
                        'category'    => $listing->mainCategory?->name ?? 'Other',
                        'brand'       => $listing->brand?->name ?? $listing->supplierAccount?->display_name ?? '—',
                        'base_price'  => $listing->base_price ? (float)$listing->base_price : null,
                        'currency'    => $listing->currency_code ?? 'USD',
                        'unit'        => $listing->unit?->abbreviation ?? $listing->unit?->symbol ?? '',
                        'is_featured' => (bool)$listing->is_featured,
                        'is_verified' => (bool)$supplierProfile,
                        'saves_count' => (int) ($savesCounts[$listing->id] ?? 0),
                        'is_saved'    => in_array($listing->id, $currentUserSavedListingIds, true),
                        'url'         => route('v2.products.show', $listing->slug),
                    ];
                }),
            ]);
        }

        return view('frontend_new.categories.index', [
            'categories'     => $categories,
            'listings'       => $listings,
            'totalProducts'  => $listings->total(),
            'activeCategory' => $categorySlug,
            'search'         => $search,
        ]);
    }
}
