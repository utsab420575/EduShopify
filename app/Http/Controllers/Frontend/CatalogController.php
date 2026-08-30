<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    private const SORTS = ['relevance', 'newest', 'price_low', 'price_high', 'rating', 'featured'];

    public function index(Request $request)
    {
        return $this->render($request, null, 'Catalog', 'Browse products and services from verified education suppliers.');
    }

    public function products(Request $request)
    {
        return $this->render($request, 'product', 'Products', 'Physical and consumable products for educational institutions.');
    }

    public function services(Request $request)
    {
        return $this->render($request, 'service', 'Services', 'Professional and support services for educational institutions.');
    }

    private function render(Request $request, ?string $type, string $title, string $subtitle)
    {
        $query = PublicListingQuery::base()->with(['mainCategory', 'brand', 'unit', 'primaryImage', 'media', 'productDetail', 'serviceDetail', 'supplierAccount.supplierProfile']);

        if ($type) {
            $query->whereHas('listingType', fn ($q) => $q->where('code', $type));
        } elseif ($request->filled('listing_type') && in_array($request->string('listing_type')->toString(), ['product', 'service'], true)) {
            $query->whereHas('listingType', fn ($q) => $q->where('code', $request->string('listing_type')));
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->string('category'))->first();
            if ($category) {
                $query->where(function (Builder $q) use ($category) {
                    $q->where('main_category_id', $category->id)
                        ->orWhereHas('categories', fn (Builder $c) => $c->where('categories.id', $category->id));
                });
            }
        }

        if ($request->filled('brand')) {
            $brand = Brand::where('slug', $request->string('brand'))->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        if ($request->filled('supplier')) {
            $supplierProfile = \App\Models\SupplierProfile::where('slug', $request->string('supplier'))->first();
            if ($supplierProfile) {
                $query->where('supplier_account_id', $supplierProfile->account_id);
            }
        }

        if ($request->filled('country')) {
            $query->whereHas('supplierAccount.supplierProfile', fn (Builder $q) => $q->where('country_id', $request->integer('country')));
        }

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->float('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->float('max_price'));
        }

        if ($request->filled('min_moq')) {
            $query->where('min_order_quantity', '>=', $request->float('min_moq'));
        }

        if ($request->filled('stock_status')) {
            $query->whereHas('productDetail', fn (Builder $q) => $q->where('stock_status', $request->string('stock_status')));
        }

        if ($request->filled('service_mode')) {
            $query->whereHas('serviceDetail', fn (Builder $q) => $q->where('service_mode', $request->string('service_mode')));
        }

        if ($request->boolean('verified')) {
            $query->whereHas('supplierAccount.supplierProfile');
        }

        $sort = in_array($request->string('sort')->toString(), self::SORTS, true) ? $request->string('sort')->toString() : 'relevance';

        match ($sort) {
            'newest' => $query->latest('published_at'),
            'price_low' => $query->orderBy('base_price'),
            'price_high' => $query->orderByDesc('base_price'),
            'featured' => $query->orderByDesc('is_featured')->latest('published_at'),
            default => $query->latest('published_at'),
        };

        $listings = $query->paginate(24)->withQueryString();

        $categories = Category::active()->approved()->roots()->orderBy('sort_order')->get();
        $brands = Brand::where('approval_status', 'approved')->where('is_active', true)->orderBy('name')->limit(40)->get();

        return view('frontend.catalog.index', [
            'listings' => $listings,
            'categories' => $categories,
            'brands' => $brands,
            'title' => $title,
            'subtitle' => $subtitle,
            'activeType' => $type,
            'sort' => $sort,
            'filters' => $request->only(['q', 'category', 'brand', 'supplier', 'country', 'min_price', 'max_price', 'min_moq', 'stock_status', 'service_mode', 'verified']),
        ]);
    }
}
