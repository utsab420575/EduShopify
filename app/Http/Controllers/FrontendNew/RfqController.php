<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\RfqPublicSummary;
use Illuminate\Http\Request;

class RfqController extends Controller
{
    public function index(Request $request)
    {
        $query = RfqPublicSummary::query()->globalVisibility()->stillOpen();

        if ($request->filled('q')) {
            $query->search($request->string('q')->toString());
        }

        $opportunities = $query->orderBy('quotation_deadline')->paginate(20)->withQueryString();

        return view('frontend_new.rfqs.index', [
            'opportunities' => $opportunities,
            'q' => $request->string('q')->toString(),
        ]);
    }

    public function show(string $rfq_number)
    {
        $opportunity = RfqPublicSummary::query()
            ->globalVisibility()
            ->where('rfq_number', $rfq_number)
            ->firstOrFail();

        // A stale/expired public link should behave like it no longer
        // exists rather than confirming a real-but-closed RFQ.
        abort_if($opportunity->quotation_deadline && $opportunity->quotation_deadline->isPast(), 404);

        $similar = RfqPublicSummary::query()
            ->globalVisibility()
            ->stillOpen()
            ->where('rfq_id', '!=', $opportunity->rfq_id)
            ->when($opportunity->category_summary, fn ($q) => $q->where('category_summary', $opportunity->category_summary))
            ->orderBy('quotation_deadline')
            ->limit(3)
            ->get();

        return view('frontend_new.rfqs.show', [
            'opportunity' => $opportunity,
            'similar' => $similar,
        ]);
    }
}
