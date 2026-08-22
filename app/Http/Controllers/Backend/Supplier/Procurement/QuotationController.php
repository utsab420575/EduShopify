<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Services\QuotationService;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->quotations()->with(['rfq.buyerAccount.buyerProfile'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $quotations = $query->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.quotations.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotations' => $quotations,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(Rfq $rfq)
    {
        $account = $this->currentAccount();

        // Check if quotation already exists
        $existing = $account->quotations()->where('rfq_id', $rfq->id)->first();
        if ($existing) {
            return redirect()->route('supplier.quotations.show', $existing);
        }

        $rfq->load(['items.unit', 'buyerAccount.buyerProfile']);

        return view('backend.supplier.procurement.quotations.create', [
            'account' => $account,
            'user' => $this->currentUser(),
            'rfq' => $rfq,
        ]);
    }

    public function store(Request $request, Rfq $rfq, QuotationService $service)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => ['nullable', 'exists:rfq_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'items.*.is_alternative' => ['nullable', 'boolean'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'warranty_terms' => ['nullable', 'string', 'max:1000'],
            'support_terms' => ['nullable', 'string', 'max:1000'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'proposal' => ['nullable', 'string', 'max:5000'],
        ]);

        $quotation = $service->submit($rfq, $account, $this->currentUser(), $validated);

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation submitted successfully.');
    }

    public function show(Quotation $quotation)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $quotation->load(['rfq.buyerAccount.buyerProfile', 'items', 'revisions.items', 'revisionRequests' => fn ($q) => $q->latest()]);

        return view('backend.supplier.procurement.quotations.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotation' => $quotation,
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $quotation->load(['rfq.items.unit', 'items']);

        return view('backend.supplier.procurement.quotations.edit', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'rfq' => $quotation->rfq,
        ]);
    }

    public function update(Request $request, Quotation $quotation, QuotationService $service)
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
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->revise($quotation, $validated, $this->currentUser());

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation updated and revision recorded.');
    }

    public function submit(Quotation $quotation)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $quotation->update(['status' => 'submitted', 'submitted_at' => now()]);

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation submitted.');
    }

    public function withdraw(Request $request, Quotation $quotation, QuotationService $service)
    {
        $account = $this->currentAccount();
        abort_if($quotation->supplier_account_id !== $account->id, 403);

        $service->withdraw($quotation, $request->input('reason'));

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation withdrawn.');
    }
}
