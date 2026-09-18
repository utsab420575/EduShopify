<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductSelectorController extends Controller
{
    /**
     * GET buyer/select-products-for-rfq — search/browse/filter marketplace
     * listings and multi-select the ones to add as RFQ items. Mirrors the
     * public /v2/categories marketplace page's look (search + category
     * tabs + product-card grid) so buyers don't hit a differently themed
     * page mid-flow, with a type toggle added since RFQ items may be
     * products or services (the public marketplace only lists products).
     *
     * The page itself never touches the RFQ; on "Add Selected" it
     * redirects back to `return_url` with the chosen listing ids, and the
     * RFQ form's Alpine component turns those into item cards client-side
     * (see resources/views/backend/buyer/procurement/rfqs/partials/_form.blade.php).
     */
    public function index(Request $request)
    {
        $type = in_array($request->string('type')->toString(), ['product', 'service'], true)
            ? $request->string('type')->toString()
            : '';

        // Root category tabs, each with a listing count across its full
        // subtree — listings are assigned to child/leaf categories, not
        // root categories directly, so a root-only match would always come
        // back empty (this is what made the category filter look broken).
        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Category $category) use ($type) {
                $ids = array_merge([$category->id], $category->descendantIds());

                $category->listing_count = Listing::published()
                    ->when($type !== '', fn ($q) => $q->whereHas('listingType', fn ($q2) => $q2->where('code', $type)))
                    ->where(function (Builder $q) use ($ids) {
                        $q->whereIn('main_category_id', $ids)
                            ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                    })
                    ->count();

                return $category;
            })
            ->filter(fn (Category $category) => $category->listing_count > 0)
            ->values();

        $listings = Listing::published()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%"));
            })
            ->when($type !== '', fn ($q) => $q->whereHas('listingType', fn ($q2) => $q2->where('code', $type)))
            ->when($request->filled('category'), function ($q) use ($request) {
                $cat = Category::find($request->integer('category'));
                if (! $cat) {
                    return;
                }
                $ids = array_merge([$cat->id], $cat->descendantIds());
                $q->where(function (Builder $q2) use ($ids) {
                    $q2->whereIn('main_category_id', $ids)
                        ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                });
            })
            ->with(['supplierAccount.supplierProfile', 'mainCategory', 'primaryImage'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // Only ever redirect back into the RFQ area — never let an
        // arbitrary `return_url` turn this into an open redirect.
        $returnUrl = $request->string('return_url')->toString();
        $rfqsBase = url('/buyer/rfqs');
        if ($returnUrl === '' || ! str_starts_with($returnUrl, $rfqsBase)) {
            $returnUrl = route('buyer.rfqs.create');
        }

        return view('backend.buyer.procurement.rfqs.select-products', [
            'listings' => $listings,
            'search' => $request->string('search')->toString(),
            'type' => $type,
            'category' => $request->integer('category'),
            'categories' => $categories,
            'returnUrl' => $returnUrl,
        ]);
    }
}
