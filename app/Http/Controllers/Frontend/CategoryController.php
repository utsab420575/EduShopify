<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->withCount(['children' => fn ($q) => $q->active()->approved()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $category->public_listing_count = PublicListingQuery::forCategory($category->id)->count();

                return $category;
            });

        return view('frontend.categories.index', [
            'categories' => $categories,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Category $category)
    {
        abort_unless($category->is_active && $category->isApproved(), 404);

        $category->load(['parent', 'children' => fn ($q) => $q->active()->approved()->orderBy('sort_order')]);

        $listings = PublicListingQuery::forCategory($category->id)
            ->with(['mainCategory', 'brand', 'supplierAccount.supplierProfile'])
            ->latest('published_at')
            ->paginate(24)
            ->withQueryString();

        return view('frontend.categories.show', [
            'category' => $category,
            'listings' => $listings,
        ]);
    }
}
