<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\SupplierProfile;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use App\Support\FrontendNewDemo;

class SupplierController extends Controller
{
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
