<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CompareDataRequest;
use App\Services\Catalog\ProductComparisonService;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, guest-accessible product/listing comparison (spec §14/§56 — no
 * auth middleware anywhere on this controller). Read-only: nothing here
 * ever writes to listings/attribute-value/variant tables. Selection state
 * lives in the client's localStorage; every page load and every mutation
 * re-fetches fresh data through data() rather than trusting anything the
 * browser already holds.
 */
class ProductComparisonController extends Controller
{
    public function index()
    {
        return view('frontend.compare.index', [
            'maxItems' => (int) config('comparison.max_items', 5),
        ]);
    }

    public function data(CompareDataRequest $request, ProductComparisonService $service): JsonResponse
    {
        $resolved = $service->resolve($request->validated()['items']);

        return response()->json([
            'listings' => $service->buildHeaders($resolved['pairs']),
            'matrix' => $service->buildMatrix($resolved['pairs']),
            'removed_ids' => $resolved['removed_ids'],
            'max_items' => (int) config('comparison.max_items', 5),
        ]);
    }

    /**
     * Lightweight "Add More" picker (spec §45). SearchController::suggestions
     * is unsuitable here — it returns {title,url,meta} for navigation, not
     * the {id} a compare picker needs to select something.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = PublicListingQuery::base()
            ->where('name', 'like', "%{$term}%")
            ->limit(8)
            ->get(['id', 'name', 'slug', 'main_category_id'])
            ->load('mainCategory')
            ->map(fn ($listing) => [
                'id' => $listing->id,
                'name' => $listing->name,
                'category' => $listing->mainCategory?->name,
                'thumb_url' => $listing->getFirstMediaUrl('gallery') ?: null,
            ]);

        return response()->json(['results' => $results]);
    }
}
