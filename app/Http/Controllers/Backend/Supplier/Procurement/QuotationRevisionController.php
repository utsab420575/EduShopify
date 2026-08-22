<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\Request;

class QuotationRevisionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function create(Quotation $quotation)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $quotation->load(['rfq.items.unit', 'items', 'revisionRequests' => fn ($q) => $q->where('status', 'pending')]);

        return view('backend.supplier.procurement.quotations.revision', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'rfq' => $quotation->rfq,
            'revisionRequest' => $quotation->revisionRequests->first(),
        ]);
    }

    public function store(Request $request, Quotation $quotation, QuotationService $service)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:quotation_items,id'],
            'items.*.rfq_item_id' => ['nullable', 'exists:rfq_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'warranty_terms' => ['nullable', 'string', 'max:1000'],
            'support_terms' => ['nullable', 'string', 'max:1000'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'proposal' => ['nullable', 'string', 'max:5000'],
            'change_summary' => ['required', 'string', 'max:1000'],
        ]);

        $service->revise($quotation, $validated, $this->currentUser());

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation revision submitted to buyer.');
    }
}
