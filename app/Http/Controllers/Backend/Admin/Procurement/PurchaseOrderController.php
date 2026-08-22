<?php

namespace App\Http\Controllers\Backend\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.rfqs.moderate');

        $purchaseOrders = PurchaseOrder::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['buyerAccount', 'supplierAccount.supplierProfile'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.procurement.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('platform.rfqs.moderate');

        $purchaseOrder->load(['buyerAccount', 'supplierAccount.supplierProfile', 'items', 'statusHistory', 'award', 'rfq']);

        return view('backend.admin.procurement.purchase-orders.show', ['purchaseOrder' => $purchaseOrder]);
    }
}
