<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RfqPublicSummary;
use App\Models\SubscriptionPlan;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;

class HomeController extends Controller
{
    public function index()
    {
        $topCategories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('sort_order')
            ->limit(12)
            ->get()
            ->map(function (Category $category) {
                $category->public_listing_count = PublicListingQuery::forCategory($category->id)->count();

                return $category;
            });

        $featuredProducts = PublicListingQuery::products()
            ->where('is_featured', true)
            ->with(['mainCategory', 'brand', 'unit', 'primaryImage', 'media', 'productDetail', 'supplierAccount.supplierProfile'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        if ($featuredProducts->count() < 4) {
            $featuredProducts = PublicListingQuery::products()
                ->with(['mainCategory', 'brand', 'unit', 'primaryImage', 'media', 'productDetail', 'supplierAccount.supplierProfile'])
                ->latest('published_at')
                ->limit(8)
                ->get();
        }

        $featuredServices = PublicListingQuery::services()
            ->where('is_featured', true)
            ->with(['mainCategory', 'primaryImage', 'media', 'serviceDetail', 'supplierAccount.supplierProfile'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        if ($featuredServices->count() < 4) {
            $featuredServices = PublicListingQuery::services()
                ->with(['mainCategory', 'primaryImage', 'media', 'serviceDetail', 'supplierAccount.supplierProfile'])
                ->latest('published_at')
                ->limit(8)
                ->get();
        }

        $featuredSuppliers = PublicSupplierQuery::base()
            ->with(['country', 'city', 'account.supplierTypes'])
            ->orderByDesc('rating')
            ->limit(4)
            ->get();

        $openRfqOpportunities = RfqPublicSummary::query()
            ->globalVisibility()
            ->stillOpen()
            ->orderBy('quotation_deadline')
            ->limit(5)
            ->get();

        $featuredPlans = SubscriptionPlan::active()->orderBy('sort_order')->limit(3)->get();

        $stats = [
            'suppliers' => PublicSupplierQuery::base()->count(),
            'categories' => Category::active()->approved()->count(),
            'listings' => PublicListingQuery::base()->count(),
        ];

        return view('frontend.home.index', [
            'topCategories' => $topCategories,
            'featuredProducts' => $featuredProducts,
            'featuredServices' => $featuredServices,
            'featuredSuppliers' => $featuredSuppliers,
            'openRfqOpportunities' => $openRfqOpportunities,
            'featuredPlans' => $featuredPlans,
            'stats' => $stats,
        ]);
    }
}
