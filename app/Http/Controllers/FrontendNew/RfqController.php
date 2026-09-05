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
}
