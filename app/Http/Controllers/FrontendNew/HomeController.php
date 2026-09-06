<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use App\Support\FrontendNewDemo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        $topCategories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        $featuredSuppliers = $this->applyBadgePattern(
            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes'])
                ->orderByDesc('rating')
                ->limit(12)
                ->get()
                ->unique('id')
                ->values()
        );

        $allSuppliers = $this->applyBadgePattern(
            $this->allSuppliersByCategory($topCategories)
        );

        // Computed independently of $allSuppliers' per-supplier category
        // assignment above: that assignment dedupes each supplier into
        // exactly one category (first match wins), so with few real
        // suppliers, one supplier matching multiple categories would
        // otherwise "use up" every category but the first — starving every
        // other real tab even though it does have a match. A tab should
        // show whenever a category has *any* matching supplier, regardless
        // of which specific supplier the grid below happened to assign it.
        $allSuppliersTabs = $topCategories->filter(function (Category $category) {
            $ids = array_merge([$category->id], $category->descendantIds());

            return PublicSupplierQuery::base()->whereHas('account.listings', function (Builder $q) use ($ids) {
                $q->whereIn('main_category_id', $ids)
                    ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
            })->exists();
        })->values();

        $blogPosts = BlogPost::published()
            ->with(['category', 'account.supplierProfile', 'account.buyerProfile', 'tags'])
            ->orderByDesc('featured')
            ->latest('published_at')
            ->limit(4)
            ->get();

        $activeCategory = request()->input('category', 'all');

        return view('frontend_new.home.index', [
            'featuredSuppliers' => $featuredSuppliers,
            'allSuppliers' => $allSuppliers,
            'allSuppliersTabs' => $allSuppliersTabs,
            'activeCategory' => $activeCategory,
            'events' => $blogPosts, // Backward compatibility alias
            'blogPosts' => $blogPosts,
        ]);
    }

    /**
     * Up to 2 suppliers per top category (tagged for the client-side tab
     * filter), topped up with generic eligible suppliers, capped at 8.
     * Accurately associates each supplier with ALL matching root category slugs.
     */
    private function allSuppliersByCategory(Collection $categories): Collection
    {
        $suppliers = collect();

        foreach ($categories as $category) {
            $ids = array_merge([$category->id], $category->descendantIds());

            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes', 'account.listings.categories'])
                ->whereHas('account.listings', function (Builder $q) use ($ids) {
                    $q->whereIn('main_category_id', $ids)
                        ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                })
                ->orderByDesc('rating')
                ->limit(2)
                ->get()
                ->each(function ($supplier) use ($category, $suppliers) {
                    if (! $suppliers->has($supplier->id)) {
                        $supplier->matched_categories = collect([$category->slug]);
                        $suppliers->put($supplier->id, $supplier);
                    } else {
                        $existing = $suppliers->get($supplier->id);
                        if ($existing->matched_categories && ! $existing->matched_categories->contains($category->slug)) {
                            $existing->matched_categories->push($category->slug);
                        }
                    }
                });
        }

        if ($suppliers->count() < 8) {
            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes', 'account.listings.categories'])
                ->whereNotIn('id', $suppliers->keys())
                ->orderByDesc('rating')
                ->limit(8 - $suppliers->count())
                ->get()
                ->each(function ($supplier) use ($categories, $suppliers) {
                    $matched = collect();
                    $listingCatIds = $supplier->account?->listings?->flatMap(function ($l) {
                        return array_merge([$l->main_category_id], $l->categories->pluck('id')->all());
                    })->filter()->unique()->all() ?? [];

                    foreach ($categories as $cat) {
                        $ids = array_merge([$cat->id], $cat->descendantIds());
                        if (! empty(array_intersect($listingCatIds, $ids))) {
                            $matched->push($cat->slug);
                        }
                    }
                    $supplier->matched_categories = $matched;
                    $suppliers->put($supplier->id, $supplier);
                });
        }

        return $suppliers->take(8)->values();
    }

    /**
     * Demo-only Founding/ISE badge flags — see config/frontend_new_demo.php
     * and App\Support\FrontendNewDemo for why these are ID-deterministic
     * rather than fabricated per-supplier facts.
     */
    private function applyBadgePattern(Collection $suppliers): Collection
    {
        return $suppliers->values()->map(function ($supplier) {
            $flags = FrontendNewDemo::supplierBadges($supplier->id);
            $supplier->demo_founding = $flags['founding'];
            $supplier->demo_ise = $flags['ise'];
            $supplier->product_count = PublicListingQuery::forSupplierAccount($supplier->account_id)->count();

            return $supplier;
        });
    }
}
