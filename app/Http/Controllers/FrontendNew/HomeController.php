<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
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
                ->limit(4)
                ->get()
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

        $events = collect(config('frontend_new_demo.events', []));

        return view('frontend_new.home.index', [
            'featuredSuppliers' => $featuredSuppliers,
            'allSuppliers' => $allSuppliers,
            'allSuppliersTabs' => $allSuppliersTabs,
            'events' => $events,
        ]);
    }

    /**
     * Up to 2 suppliers per top category (tagged for the client-side tab
     * filter), topped up with generic eligible suppliers, capped at 8.
     */
    private function allSuppliersByCategory(Collection $categories): Collection
    {
        $suppliers = collect();

        foreach ($categories as $category) {
            // Real listings are tagged at child/leaf categories (e.g.
            // "Laptop" under root "Laptop & Netbook"), not the root itself
            // — match the whole subtree, or every root's count is always 0.
            $ids = array_merge([$category->id], $category->descendantIds());

            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes'])
                ->whereHas('account.listings', function (Builder $q) use ($ids) {
                    $q->whereIn('main_category_id', $ids)
                        ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                })
                ->orderByDesc('rating')
                ->limit(2)
                ->get()
                ->each(function ($supplier) use ($category, $suppliers) {
                    if (! $suppliers->has($supplier->id)) {
                        $supplier->home_category = $category;
                        $suppliers->put($supplier->id, $supplier);
                    }
                });
        }

        if ($suppliers->count() < 8) {
            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes'])
                ->whereNotIn('id', $suppliers->keys())
                ->orderByDesc('rating')
                ->limit(8 - $suppliers->count())
                ->get()
                ->each(fn ($supplier) => $suppliers->put($supplier->id, $supplier));
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
