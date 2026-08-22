<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RfqPublicSummary;
use Illuminate\Http\Request;

class PublicRfqController extends Controller
{
    public function index(Request $request)
    {
        $query = RfqPublicSummary::query()->globalVisibility()->stillOpen();

        if ($request->filled('q')) {
            $query->search($request->string('q')->toString());
        }

        if ($request->filled('category')) {
            $query->where('category_summary', 'like', '%'.$request->string('category').'%');
        }

        if ($request->filled('item_type')) {
            $query->where('item_types', 'like', '%'.$request->string('item_type').'%');
        }

        if ($request->filled('country')) {
            $query->inCountry($request->integer('country'));
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'newest' => $query->latest('published_at'),
            default => $query->orderBy('quotation_deadline'),
        };

        $opportunities = $query->paginate(20)->withQueryString();

        return view('frontend.rfqs.index', [
            'opportunities' => $opportunities,
            'sort' => $sort ?: 'deadline',
            'filters' => $request->only(['q', 'category', 'item_type', 'country']),
        ]);
    }

    public function show(string $rfqNumber)
    {
        $opportunity = RfqPublicSummary::query()
            ->globalVisibility()
            ->where('rfq_number', $rfqNumber)
            ->firstOrFail();

        // A stale/expired public link should behave like it no longer exists
        // rather than confirming a real-but-closed RFQ (Part 73).
        abort_if($opportunity->quotation_deadline && $opportunity->quotation_deadline->isPast(), 404);

        return view('frontend.rfqs.show', [
            'opportunity' => $opportunity,
        ]);
    }
}
