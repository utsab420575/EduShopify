<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\SavedItem;
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

        $this->attachSavesData($suppliers);

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

        $services = $supplier->account->services()->where('status', 'active')->with('icon')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $certifications = $supplier->account->certifications()->approved()->latest()->get();

        // "Certifications & Partners" (About tab) and "Industry Partnerships"
        // (Certifications tab) both show the same admin-approved achievement
        // claims — real badges the supplier has earned, not two different
        // generic pill lists.
        $achievements = $supplier->account->accountAchievements()
            ->approved()
            ->with('achievement')
            ->get()
            ->pluck('achievement')
            ->filter();

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

        // If cached aggregates on the profile are zero but published reviews exist (e.g. seeded), auto-sync.
        if ((int) ($supplier->reviews_count ?? 0) === 0 && $reviews->isNotEmpty()) {
            $stats = Review::where('supplier_account_id', $supplier->account_id)
                ->supplier()
                ->published()
                ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
                ->first();

            if ($stats && $stats->cnt > 0) {
                $supplier->rating = round((float) $stats->avg_rating, 2);
                $supplier->reviews_count = (int) $stats->cnt;
                $supplier->saveQuietly();
            }
        }

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

        $this->attachSingleSupplierSavesData($supplier);
        $this->attachSavesData($similarSuppliers);

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
            'services' => $services,
            'certifications' => $certifications,
            'achievements' => $achievements,
        ]);
    }

    /**
     * Toggle saving/favoriting a supplier for the authenticated user/account.
     */
    public function toggleSave(Request $request, SupplierProfile $supplier)
    {
        $user = $request->user();
        if (! $user) {
            session()->forget('frontend_intent');
            $returnUrl = url()->previous() ?: route('v2.suppliers.show', $supplier->slug);
            session(['url.intended' => $returnUrl]);

            return response()->json([
                'success'       => false,
                'authenticated' => false,
                'message'       => 'Please sign in to save suppliers to your favorites.',
                'redirect'      => route('login', ['redirect' => $returnUrl]),
            ], 401);
        }

        $account = $user->activateTeamContext()
            ?? $user->accountMember?->account
            ?? $user->account
            ?? \App\Models\Account::where('is_system_account', true)->first()
            ?? \App\Models\Account::first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for your profile.',
            ], 403);
        }

        $existing = SavedItem::where('account_id', $account->id)
            ->where('item_type', 'supplier')
            ->where('item_id', $supplier->account_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
            $message = 'Supplier removed from favorites.';
        } else {
            SavedItem::create([
                'account_id'       => $account->id,
                'saved_by_user_id' => $user->id,
                'visibility'       => 'account',
                'item_type'        => 'supplier',
                'item_id'          => $supplier->account_id,
            ]);
            $saved = true;
            $message = 'Supplier saved to your favorites!';
        }

        $savesCount = SavedItem::where('item_type', 'supplier')
            ->where('item_id', $supplier->account_id)
            ->count();

        return response()->json([
            'success'     => true,
            'saved'       => $saved,
            'saves_count' => $savesCount,
            'message'     => $message,
        ]);
    }

    /**
     * Batch attach saves_count and is_saved to a collection or paginator of suppliers.
     */
    public function attachSavesData($suppliers)
    {
        $items = $suppliers instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $suppliers->items()
            : $suppliers;

        $accountIds = collect($items)->pluck('account_id')->filter()->unique()->all();
        if (empty($accountIds)) {
            return $suppliers;
        }

        $savesCounts = SavedItem::where('item_type', 'supplier')
            ->whereIn('item_id', $accountIds)
            ->selectRaw('item_id, count(*) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->all();

        $currentUserSavedIds = [];
        if (auth()->check()) {
            $user = auth()->user();
            $currentAccount = $user->activateTeamContext()
                ?? $user->accountMember?->account
                ?? $user->account;
            if ($currentAccount) {
                $currentUserSavedIds = SavedItem::where('account_id', $currentAccount->id)
                    ->where('item_type', 'supplier')
                    ->whereIn('item_id', $accountIds)
                    ->pluck('item_id')
                    ->all();
            }
        }

        foreach ($items as $supplier) {
            $supplier->saves_count = (int) ($savesCounts[$supplier->account_id] ?? 0);
            $supplier->is_saved = in_array($supplier->account_id, $currentUserSavedIds, true);
        }

        return $suppliers;
    }

    /**
     * Attach saves_count and is_saved to a single supplier profile.
     */
    public function attachSingleSupplierSavesData(SupplierProfile $supplier): SupplierProfile
    {
        $supplier->saves_count = (int) SavedItem::where('item_type', 'supplier')
            ->where('item_id', $supplier->account_id)
            ->count();

        $supplier->is_saved = false;
        if (auth()->check()) {
            $user = auth()->user();
            $currentAccount = $user->activateTeamContext()
                ?? $user->accountMember?->account
                ?? $user->account;
            if ($currentAccount) {
                $supplier->is_saved = SavedItem::where('account_id', $currentAccount->id)
                    ->where('item_type', 'supplier')
                    ->where('item_id', $supplier->account_id)
                    ->exists();
            }
        }

        return $supplier;
    }
}
