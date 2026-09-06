<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontendNew\CompareDataRequest;
use App\Services\Catalog\ProductComparisonService;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, guest-accessible product/listing comparison for the v2 frontend —
 * a straight restyle of App\Http\Controllers\Frontend\ProductComparisonController
 * onto frontend_new views. No DB writes; selection state lives in the
 * client's localStorage (see resources/js/frontend_new/compare.js), and
 * ProductComparisonService remains the single authoritative source for
 * price/specs/supplier/availability, same as the legacy /compare page.
 */
class CompareController extends Controller
{
    public function index()
    {
        return view('frontend_new.compare.index', [
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
