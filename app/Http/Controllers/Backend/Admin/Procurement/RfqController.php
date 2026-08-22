<?php

namespace App\Http\Controllers\Backend\Admin\Procurement;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Rfq;
use App\Services\RfqService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RfqController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.rfqs.moderate');

        $rfqs = Rfq::query()
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with('buyerAccount')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.procurement.rfqs.index', [
            'rfqs' => $rfqs,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Rfq $rfq)
    {
        $this->authorize('platform.rfqs.moderate');

        $rfq->load(['buyerAccount', 'items.category', 'items.unit', 'quotations.supplierAccount.supplierProfile', 'awards', 'purchaseOrders']);

        return view('backend.admin.procurement.rfqs.show', ['rfq' => $rfq]);
    }

    public function approve(Rfq $rfq, RfqService $service)
    {
        $this->authorize('platform.rfqs.moderate');

        try {
            $service->approve($rfq, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($rfq)->log('RFQ approved and published');

        return back()->with('success', 'RFQ approved and published.');
    }

    public function cancel(ReasonRequest $request, Rfq $rfq, RfqService $service)
    {
        $this->authorize('platform.rfqs.moderate');

        try {
            $service->cancel($rfq, $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($rfq)
            ->withProperties(['reason' => $request->string('reason')])->log('RFQ cancelled by admin');

        return back()->with('success', 'RFQ cancelled.');
    }
}
