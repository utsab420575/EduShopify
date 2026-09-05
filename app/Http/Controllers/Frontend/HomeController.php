<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    public function index()
    {
        $topCategories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->limit(12)
            ->get()
            ->map(function (Category $category) {
                $category->public_listing_count = PublicListingQuery::forCategory($category->id)->count();

                return $category;
            });

        $featuredSuppliers = PublicSupplierQuery::base()
            ->with(['country', 'city', 'account.supplierTypes'])
            ->orderByDesc('rating')
            ->limit(4)
            ->get();

        $allSuppliers = $this->allSuppliersByCategory($topCategories->take(4));

        $stats = [
            'suppliers' => PublicSupplierQuery::base()->count(),
            'countries' => (clone PublicSupplierQuery::base())->whereNotNull('country_id')->distinct('country_id')->count('country_id'),
            'products' => PublicListingQuery::products()->count(),
            'buyers' => Account::where('status', 'active')
                ->whereHas('buyerCapability', fn (Builder $q) => $q->where('status', 'active'))
                ->count(),
        ];

        return view('frontend.home.index', [
            'topCategories' => $topCategories,
            'featuredSuppliers' => $featuredSuppliers,
            'allSuppliers' => $allSuppliers,
            'allSuppliersTabs' => $allSuppliers->pluck('home_category')->filter()->unique('slug')->values(),
            'stats' => $stats,
        ]);
    }

    /**
     * Up to 2 suppliers per top category (tagged for client-side tab
     * filtering), topped up with generic eligible suppliers, capped at 8.
     */
    private function allSuppliersByCategory($categories)
    {
        $suppliers = collect();

        foreach ($categories as $category) {
            PublicSupplierQuery::base()
                ->with(['country', 'city', 'account.supplierTypes'])
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
                ->with(['country', 'city', 'account.supplierTypes'])
                ->whereNotIn('id', $suppliers->keys())
                ->orderByDesc('rating')
                ->limit(8 - $suppliers->count())
                ->get()
                ->each(fn ($supplier) => $suppliers->put($supplier->id, $supplier));
        }

        return $suppliers->take(8)->values();
    }
}
