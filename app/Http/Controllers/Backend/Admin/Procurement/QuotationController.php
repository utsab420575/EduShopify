<?php

namespace App\Http\Controllers\Backend\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.rfqs.moderate');

        $quotations = Quotation::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['rfq', 'supplierAccount.supplierProfile'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.procurement.quotations.index', [
            'quotations' => $quotations,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('platform.rfqs.moderate');

        $quotation->load(['rfq', 'supplierAccount.supplierProfile', 'items', 'revisions', 'award']);

        return view('backend.admin.procurement.quotations.show', ['quotation' => $quotation]);
    }
}
