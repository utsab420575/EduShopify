<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Procurement\ReviseQuotationRequest;
use App\Models\Currency;
use App\Models\Quotation;
use App\Models\Unit;
use App\Services\QuotationService;

class QuotationRevisionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function create(Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        $quotation->load([
            'rfq.items.unit', 'rfq.items.category', 'rfq.items.attributeValues.attribute.unit', 'rfq.items.attributeValues.attributeValue',
            'items.attributeValues',
            'revisionRequests' => fn ($q) => $q->where('status', 'pending'),
        ]);

        return view('backend.supplier.procurement.quotations.revision', [
            'account' => $this->currentAccount(),
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'rfq' => $quotation->rfq,
            'revisionRequest' => $quotation->revisionRequests->first(),
            'units' => Unit::active()->orderBy('name')->get(['id', 'name', 'symbol']),
            'currencies' => Currency::active()->orderBy('code')->get(['code', 'name', 'symbol']),
        ]);
    }

    public function store(ReviseQuotationRequest $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('update', $quotation);

        $service->revise($quotation, $request->validated(), $this->currentUser());

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation revision submitted to buyer.');
    }
}
