<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $category) {
                $category->listing_count = PublicListingQuery::forCategory($category->id)->count();
                $category->supplier_count = PublicSupplierQuery::base()
                    ->whereHas('account.listings', function (Builder $q) use ($category) {
                        $q->where('main_category_id', $category->id)
                            ->orWhereHas('categories', fn (Builder $c) => $c->where('categories.id', $category->id));
                    })
                    ->count();

                return $category;
            });

        return view('frontend_new.categories.index', [
            'categories' => $categories,
        ]);
    }
}
