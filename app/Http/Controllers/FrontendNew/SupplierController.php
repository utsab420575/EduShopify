<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\SupplierProfile;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use App\Support\FrontendNewDemo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Same PublicSupplierQuery eligibility already proven in the legacy
     * App\Http\Controllers\Frontend\SupplierDirectoryController, restyled
     * to the category-tabs + live-search pattern established by the
     * Marketplace/Categories page — kept independent of the legacy
     * Frontend\* namespace.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $categorySlug = $request->input('category', 'all');

        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $category) {
                $category->supplier_count = $this->suppliersInCategoryTree($category)->count();

                return $category;
            });

        $query = PublicSupplierQuery::base()->with(['country', 'account.supplierTypes']);

        if ($categorySlug && $categorySlug !== 'all') {
            $activeCategory = Category::where('slug', $categorySlug)->first();
            if ($activeCategory) {
                $ids = array_merge([$activeCategory->id], $activeCategory->descendantIds());
                $query->whereHas('account.listings', function (Builder $q) use ($ids) {
                    $q->whereIn('main_category_id', $ids)
                        ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                });
            }
        }

        if ($search) {
            $query->where('display_name', 'like', "%{$search}%");
        }

        $query->orderByDesc('rating');

        $suppliers = $query->paginate(24)->withQueryString()->through(function ($supplier) {
            $flags = FrontendNewDemo::supplierBadges($supplier->id);
            $supplier->demo_founding = $flags['founding'];
            $supplier->demo_ise = $flags['ise'];
            $supplier->product_count = PublicListingQuery::forSupplierAccount($supplier->account_id)->count();

            return $supplier;
        });

        $data = [
            'categories' => $categories,
            'suppliers' => $suppliers,
            'totalSuppliers' => $suppliers->total(),
            'activeCategory' => $categorySlug,
            'search' => $search,
        ];

        // Live search/tab requests (fetch(), not a normal navigation) get
        // just the results fragment re-rendered — same partial, same data,
        // so tabs/counts/pagination all stay correct for the live query
        // instead of only a page-load doing that.
        if ($request->ajax()) {
            return view('frontend_new.suppliers.partials._content', $data);
        }

        return view('frontend_new.suppliers.index', $data);
    }

    /**
     * A root category's tab should match suppliers whose listings are
     * tagged anywhere in that category's subtree, not only the root
     * itself — real listings here are tagged at child level (e.g. "Laptop"
     * under root "Laptop & Netbook"), so a root-only match always finds 0.
     * Uses Category::descendantIds() (also relied on by CategoryController
     * and HomeController) rather than a private tree-walk here.
     */
    private function suppliersInCategoryTree(Category $category): Builder
    {
        $ids = array_merge([$category->id], $category->descendantIds());

        return PublicSupplierQuery::base()->whereHas('account.listings', function (Builder $q) use ($ids) {
            $q->whereIn('main_category_id', $ids)
                ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
        });
    }

    public function show(SupplierProfile $supplier)
    {
        abort_unless(PublicSupplierQuery::base()->whereKey($supplier->id)->exists(), 404);

        $supplier->load([
            'country',
            'city',
            'account.supplierTypes',
            'account.socialLinks',
            'businessHours',
            'videos' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        $featuredProducts = PublicListingQuery::forSupplierAccount($supplier->account_id)
            ->where('is_featured', true)->with('brand')->limit(4)->get();
        if ($featuredProducts->count() < 4) {
            $featuredProducts = PublicListingQuery::forSupplierAccount($supplier->account_id)->with('brand')->limit(4)->get();
        }

        $allProducts = PublicListingQuery::forSupplierAccount($supplier->account_id)->with('brand')->limit(6)->get();
        $productCount = PublicListingQuery::forSupplierAccount($supplier->account_id)->count();

        $reviews = Review::where('supplier_account_id', $supplier->account_id)
            ->supplier()
            ->published()
            ->with(['buyerAccount.buyerProfile', 'reply' => fn ($q) => $q->published()])
            ->latest('published_at')
            ->limit(10)
            ->get();

        $rfqsCompleted = Quotation::where('supplier_account_id', $supplier->account_id)
            ->where('status', 'awarded')
            ->count();

        $countriesServed = $supplier->serviceAreas()->whereNotNull('country_id')->distinct('country_id')->count('country_id');

        $avgResponseHours = $supplier->average_response_minutes
            ? round($supplier->average_response_minutes / 60, 1)
            : null;

        $yearsInBusiness = $supplier->founded_year ? now()->year - $supplier->founded_year : null;

        $similarSuppliers = PublicSupplierQuery::base()
            ->where('id', '!=', $supplier->id)
            ->with(['country', 'account.supplierTypes'])
            ->orderByDesc('rating')
            ->limit(4)
            ->get()
            ->map(function ($s) {
                $flags = FrontendNewDemo::supplierBadges($s->id);
                $s->demo_founding = $flags['founding'];
                $s->demo_ise = $flags['ise'];
                $s->product_count = PublicListingQuery::forSupplierAccount($s->account_id)->count();

                return $s;
            });

        $badges = FrontendNewDemo::supplierBadges($supplier->id);

        return view('frontend_new.suppliers.show', [
            'supplier' => $supplier,
            'featuredProducts' => $featuredProducts,
            'allProducts' => $allProducts,
            'productCount' => $productCount,
            'reviews' => $reviews,
            'rfqsCompleted' => $rfqsCompleted,
            'countriesServed' => $countriesServed,
            'avgResponseHours' => $avgResponseHours,
            'yearsInBusiness' => $yearsInBusiness,
            'similarSuppliers' => $similarSuppliers,
            'demoFounding' => $badges['founding'],
            'demoIse' => $badges['ise'],
            'demoBett' => $badges['bett'],
            'services' => config('frontend_new_demo.supplier_services', []),
            'certifications' => config('frontend_new_demo.supplier_certifications', []),
            'industryPartnerships' => config('frontend_new_demo.supplier_industry_partnerships', []),
        ]);
    }
}
