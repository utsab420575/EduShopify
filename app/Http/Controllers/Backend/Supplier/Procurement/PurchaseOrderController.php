<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->supplierPurchaseOrders()->with(['rfq', 'buyerAccount.buyerProfile'])->latest('issued_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $purchaseOrders = $query->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.purchase-orders.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'purchaseOrders' => $purchaseOrders,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $account = $this->currentAccount();
        abort_if($purchaseOrder->supplier_account_id !== $account->id, 403);

        $purchaseOrder->load(['rfq', 'buyerAccount.buyerProfile', 'items', 'statusHistory.changedBy']);

        return view('backend.supplier.procurement.purchase-orders.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'purchaseOrder' => $purchaseOrder,
        ]);
    }
}
