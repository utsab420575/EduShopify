<?php

namespace App\Http\Controllers\Backend\Buyer\Marketplace;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $listings = Listing::published()->services()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($q2) => $q2->where('categories.id', $request->integer('category'))))
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_account_id', $request->integer('supplier')))
            ->with(['supplierAccount.supplierProfile', 'mainCategory', 'serviceDetail'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('backend.buyer.marketplace.services.index', [
            'listings' => $listings,
            'savedIds' => $account->savedItems()->ofType('listing')->pluck('item_id'),
            'search' => $request->string('search')->toString(),
            'category' => $request->integer('category'),
            'categories' => Category::active()->approved()->roots()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless($listing->isPublished(), 404);

        $account = $this->currentAccount();

        $listing->load(['serviceDetail', 'supplierAccount.supplierProfile', 'mainCategory', 'categories', 'attributeValues.attribute', 'attributeValues.attributeValue']);

        return view('backend.buyer.marketplace.services.show', [
            'listing' => $listing,
            'isSaved' => $account->savedItems()->ofType('listing')->where('item_id', $listing->id)->exists(),
        ]);
    }
}
