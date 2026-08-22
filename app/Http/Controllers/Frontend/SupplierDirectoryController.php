<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SupplierProfile;
use App\Models\SupplierType;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SupplierDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicSupplierQuery::base()->with(['country', 'state', 'city', 'account.supplierTypes']);

        if ($request->filled('q')) {
            $query->where('display_name', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('type')) {
            $query->whereHas('account.supplierTypes', fn (Builder $q) => $q->where('slug', $request->string('type')));
        }

        if ($request->filled('country')) {
            $query->where('country_id', $request->integer('country'));
        }

        if ($request->filled('state')) {
            $query->where('state_id', $request->integer('state'));
        }

        if ($request->filled('category')) {
            $query->whereHas('account.listings', function (Builder $q) use ($request) {
                $q->whereHas('categories', fn (Builder $c) => $c->where('categories.slug', $request->string('category')))
                    ->orWhereHas('mainCategory', fn (Builder $c) => $c->where('slug', $request->string('category')));
            });
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'rating' => $query->orderByDesc('rating'),
            'newest' => $query->latest('profile_completed_at'),
            default => $query->orderByDesc('rating'),
        };

        $suppliers = $query->paginate(24)->withQueryString();

        return view('frontend.suppliers.index', [
            'suppliers' => $suppliers,
            'supplierTypes' => SupplierType::orderBy('name')->get(),
            'sort' => $sort ?: 'rating',
            'filters' => $request->only(['q', 'type', 'country', 'state', 'category']),
        ]);
    }

    public function show(SupplierProfile $supplier)
    {
        abort_unless(PublicSupplierQuery::base()->whereKey($supplier->id)->exists(), 404);

        $supplier->load([
            'country', 'state', 'city',
            'account.supplierTypes',
            'account.exhibitions',
            'gallery',
            'videos',
            'businessHours',
        ]);

        $tab = in_array(request('tab'), ['all', 'products', 'services'], true) ? request('tab') : 'all';

        $listingsQuery = PublicListingQuery::forSupplierAccount($supplier->account_id)
            ->with(['mainCategory', 'brand']);

        if ($tab !== 'all') {
            $listingsQuery->where('listing_type', rtrim($tab, 's'));
        }

        $listings = $listingsQuery->latest('published_at')->paginate(12)->withQueryString();

        $reviews = \App\Models\Review::where('supplier_account_id', $supplier->account_id)
            ->where('status', 'published')
            ->with(['buyerAccount', 'reply' => fn ($q) => $q->where('status', 'published')])
            ->latest('published_at')
            ->paginate(10, ['*'], 'reviews_page');

        return view('frontend.suppliers.show', [
            'supplier' => $supplier,
            'listings' => $listings,
            'reviews' => $reviews,
            'tab' => $tab,
            'productCount' => PublicListingQuery::forSupplierAccount($supplier->account_id)->where('listing_type', 'product')->count(),
            'serviceCount' => PublicListingQuery::forSupplierAccount($supplier->account_id)->where('listing_type', 'service')->count(),
        ]);
    }
}
