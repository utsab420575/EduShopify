<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\SavedItem;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Billing\FeaturedSupplierService;
use App\Services\Catalog\PublicListingQuery;
use App\Support\FrontendNewDemo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        // Suppliers with the `featured_supplier` benefit (override → snapshot →
        // plan → default), highest rating first, max 20; topped up to 4 with
        // the best-rated others. Genuinely featured ones carry is_featured_supplier.
        $featuredSuppliers = $this->attachSavesData(
            $this->applyBadgePattern(app(FeaturedSupplierService::class)->forHomepage(max: 20, min: 4))
        );

        [$allSuppliersGrid, $allSuppliersTabs] = $this->allSuppliersByCategory();

        $allSuppliers = $this->attachSavesData($this->applyBadgePattern($allSuppliersGrid));

        $blogPosts = BlogPost::published()
            ->with(['category', 'account.supplierProfile', 'account.buyerProfile', 'tags'])
            ->orderByDesc('featured')
            ->latest('published_at')
            ->limit(4)
            ->get();

        // Only a tab that exists can be pre-selected (?category=slug); anything else → All.
        $activeCategory = request()->input('category', 'all');
        if (! $allSuppliersTabs->contains('slug', $activeCategory)) {
            $activeCategory = 'all';
        }

        return view('frontend_new.home.index', [
            'featuredSuppliers' => $featuredSuppliers,
            'allSuppliers' => $allSuppliers,
            'allSuppliersTabs' => $allSuppliersTabs,
            'activeCategory' => $activeCategory,
            'events' => $blogPosts, // Backward compatibility alias
            'blogPosts' => $blogPosts,
        ]);
    }

    /**
     * "All Suppliers" grid + its category tabs, from supplier_categories
     * (what each supplier says it supplies), matched to top-level categories:
     * a supplier counts under a tab when any of its active supplier_categories
     * is that root category or anywhere beneath it (suppliers mostly pick
     * sub-categories, e.g. Mouse → Accessories).
     *
     *  - Grid: up to 2 top-rated suppliers per root category, topped up with
     *    the highest-rated remaining suppliers, max 8.
     *  - Each card is tagged with ALL of its root categories (not only the one
     *    it was picked for), so it shows under every tab it belongs to.
     *  - Tabs: exactly the root categories with at least one card in the grid,
     *    so no tab ever opens empty.
     *
     * @return array{0: Collection, 1: Collection} [suppliers, tab categories]
     */
    private function allSuppliersByCategory(int $limit = 8, int $perCategory = 2): array
    {
        // Active, approved category tree, resolved to roots in memory.
        $categories = Category::query()->active()->approved()
            ->get(['id', 'parent_id', 'name', 'slug', 'sort_order'])
            ->keyBy('id');

        $rootOf = function (int $id) use ($categories): ?int {
            for ($depth = 0; $depth < 25 && isset($categories[$id]); $depth++) {
                $parent = $categories[$id]->parent_id;
                if ($parent === null) {
                    return $id;
                }
                $id = (int) $parent;
            }

            return null; // an ancestor is inactive/unapproved (or a broken loop)
        };

        $roots = $categories->whereNull('parent_id')->sortBy([['sort_order', 'asc'], ['name', 'asc']]);
        $treeIdsByRoot = $roots->keys()->mapWithKeys(fn ($rootId) => [$rootId => []])->all();
        foreach ($categories->keys() as $id) {
            if (($root = $rootOf($id)) !== null) {
                $treeIdsByRoot[$root][] = $id;
            }
        }

        $withSupplierCategories = fn (array $categoryIds) => fn (Builder $q) => $q->where('is_active', true)->whereIn('category_id', $categoryIds);

        // 1. Up to $perCategory top-rated suppliers for each root category.
        $pickedIds = collect();
        foreach ($roots as $rootId => $root) {
            $pickedIds = $pickedIds->merge(
                PublicSupplierQuery::base()
                    ->whereHas('account.supplierCategories', $withSupplierCategories($treeIdsByRoot[$rootId]))
                    ->whereNotIn('supplier_profiles.id', $pickedIds)
                    ->orderByDesc('rating')
                    ->limit($perCategory)
                    ->pluck('supplier_profiles.id')
            );
            if ($pickedIds->count() >= $limit) {
                break;
            }
        }
        $pickedIds = $pickedIds->unique()->take($limit);

        // 2. Top up with the highest-rated remaining suppliers.
        if ($pickedIds->count() < $limit) {
            $pickedIds = $pickedIds->merge(
                PublicSupplierQuery::base()
                    ->whereNotIn('supplier_profiles.id', $pickedIds)
                    ->orderByDesc('rating')
                    ->limit($limit - $pickedIds->count())
                    ->pluck('supplier_profiles.id')
            );
        }

        $suppliers = PublicSupplierQuery::base()
            ->whereIn('supplier_profiles.id', $pickedIds)
            ->with(array_merge(
                ['account.supplierCategories' => fn ($q) => $q->where('is_active', true)],
                \App\Models\SupplierProfile::publicCardRelations()
            ))
            ->get()
            ->sortBy(fn ($supplier) => $pickedIds->search($supplier->id)) // keep pick order
            ->values();

        // 3. Tag each supplier with every root category its supplier_categories fall under.
        foreach ($suppliers as $supplier) {
            $supplier->matched_categories = ($supplier->account?->supplierCategories ?? collect())
                ->map(fn ($row) => $rootOf((int) $row->category_id))
                ->filter(fn ($rootId) => $rootId !== null && isset($roots[$rootId]))
                ->unique()
                ->map(fn ($rootId) => $roots[$rootId]->slug)
                ->values();
        }

        // 4. Tabs: root categories that have at least one supplier in the grid.
        $usedSlugs = $suppliers->flatMap(fn ($supplier) => $supplier->matched_categories)->unique();
        $tabs = $roots->filter(fn ($root) => $usedSlugs->contains($root->slug))->values();

        return [$suppliers, $tabs];
    }

    /**
     * Demo-only Founding/ISE badge flags — see config/frontend_new_demo.php
     * and App\Support\FrontendNewDemo for why these are ID-deterministic
     * rather than fabricated per-supplier facts.
     */
    private function applyBadgePattern(Collection $suppliers): Collection
    {
        return $suppliers->values()->map(function ($supplier) {
            $flags = FrontendNewDemo::supplierBadges($supplier->id);
            $supplier->demo_founding = $flags['founding'];
            $supplier->demo_ise = $flags['ise'];
            $supplier->product_count = PublicListingQuery::forSupplierAccount($supplier->account_id)->count();

            return $supplier;
        });
    }

    /**
     * Batch attach saves_count and is_saved to a collection of suppliers.
     */
    private function attachSavesData(Collection $suppliers): Collection
    {
        $accountIds = $suppliers->pluck('account_id')->filter()->unique()->all();
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

        foreach ($suppliers as $supplier) {
            $supplier->saves_count = (int) ($savesCounts[$supplier->account_id] ?? 0);
            $supplier->is_saved = in_array($supplier->account_id, $currentUserSavedIds, true);
        }

        return $suppliers;
    }
}
