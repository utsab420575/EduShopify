<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
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

        $allSuppliersTabs = $allSuppliers->pluck('home_category')->filter()->unique('slug')->values();

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
            PublicSupplierQuery::base()
                ->with(['country', 'account.supplierTypes'])
                ->whereHas('account.listings', function (Builder $q) use ($category) {
                    $q->where('main_category_id', $category->id)
                        ->orWhereHas('categories', fn (Builder $c) => $c->where('categories.id', $category->id));
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
     * Demo-only Founding/ISE badge flags applied by position — see
     * config/frontend_new_demo.php for why.
     */
    private function applyBadgePattern(Collection $suppliers): Collection
    {
        $pattern = config('frontend_new_demo.supplier_badge_pattern', []);

        return $suppliers->values()->map(function ($supplier, $index) use ($pattern) {
            $flags = $pattern[$index % max(count($pattern), 1)] ?? ['founding' => false, 'ise' => false];
            $supplier->demo_founding = $flags['founding'];
            $supplier->demo_ise = $flags['ise'];
            $supplier->product_count = PublicListingQuery::forSupplierAccount($supplier->account_id)->count();

            return $supplier;
        });
    }
}
