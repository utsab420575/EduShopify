<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RfqPublicSummary;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['query' => $term, 'groups' => []]);
        }

        $products = PublicListingQuery::products()
            ->where('name', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'name', 'slug', 'base_price', 'currency_code'])
            ->map(fn ($l) => ['title' => $l->name, 'url' => route('frontend.listings.show', $l->slug), 'meta' => 'Product']);

        $services = PublicListingQuery::services()
            ->where('name', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(fn ($l) => ['title' => $l->name, 'url' => route('frontend.listings.show', $l->slug), 'meta' => 'Service']);

        $suppliers = PublicSupplierQuery::base()
            ->where('display_name', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'display_name', 'slug'])
            ->map(fn ($s) => ['title' => $s->display_name, 'url' => route('frontend.suppliers.show', $s->slug), 'meta' => 'Supplier']);

        $rfqs = RfqPublicSummary::query()
            ->globalVisibility()
            ->stillOpen()
            ->search($term)
            ->limit(5)
            ->get(['rfq_id', 'rfq_number', 'title'])
            ->map(fn ($r) => ['title' => $r->title, 'url' => route('frontend.rfqs.show', $r->rfq_number), 'meta' => 'RFQ Opportunity']);

        $groups = collect([
            'Products' => $products,
            'Services' => $services,
            'Suppliers' => $suppliers,
            'RFQ Opportunities' => $rfqs,
        ])->filter(fn ($items) => $items->isNotEmpty())->map(fn ($items, $label) => [
            'label' => $label,
            'items' => $items->values(),
        ])->values();

        return response()->json([
            'query' => $term,
            'groups' => $groups,
            'view_all_url' => route('frontend.catalog.index', ['q' => $term]),
        ]);
    }
}
