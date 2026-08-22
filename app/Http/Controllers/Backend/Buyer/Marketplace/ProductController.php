<?php

namespace App\Http\Controllers\Backend\Buyer\Marketplace;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Services\SavedItemService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $listings = Listing::published()->products()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($q2) => $q2->where('categories.id', $request->integer('category'))))
            ->when($request->filled('brand'), fn ($q) => $q->where('brand_id', $request->integer('brand')))
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_account_id', $request->integer('supplier')))
            ->when($request->filled('min_price'), fn ($q) => $q->where('base_price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('base_price', '<=', $request->float('max_price')))
            ->with(['supplierAccount.supplierProfile', 'mainCategory', 'brand'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('backend.buyer.marketplace.products.index', [
            'listings' => $listings,
            'savedIds' => $account->savedItems()->ofType('listing')->pluck('item_id'),
            'search' => $request->string('search')->toString(),
            'category' => $request->integer('category'),
            'brand' => $request->integer('brand'),
            'categories' => Category::active()->approved()->roots()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::where('approval_status', 'approved')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless($listing->isPublished(), 404);

        $account = $this->currentAccount();

        $listing->load([
            'productDetail', 'supplierAccount.supplierProfile', 'mainCategory', 'categories', 'brand', 'unit',
            'attributeValues.attribute', 'attributeValues.attributeValue',
            'variants.attributeValues', 'tierPrices',
        ]);

        return view('backend.buyer.marketplace.products.show', [
            'listing' => $listing,
            'isSaved' => $account->savedItems()->ofType('listing')->where('item_id', $listing->id)->exists(),
        ]);
    }
}
