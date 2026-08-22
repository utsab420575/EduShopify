<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Review;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $account = $this->currentAccount();

        $orders = $account->buyerPurchaseOrders()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['rfq', 'supplierAccount.supplierProfile'])
            ->latest('issued_at')
            ->paginate(10)
            ->withQueryString();

        return view('backend.buyer.procurement.purchase-orders.index', [
            'orders' => $orders,
            'status' => $request->string('status')->toString(),
            'statusOptions' => [
                'issued' => 'Issued', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress',
                'ready_for_delivery' => 'Ready for Delivery', 'delivered' => 'Delivered',
                'completed' => 'Completed', 'cancelled' => 'Cancelled', 'disputed' => 'Disputed',
            ],
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load([
            'items.quotationItem.offeredListing', 'items.unit',
            'supplierAccount.supplierProfile', 'rfq', 'quotation',
            'statusHistory' => fn ($q) => $q->latest(),
        ]);

        return view('backend.buyer.procurement.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
            'canReview' => \Illuminate\Support\Facades\Gate::allows('createForPurchaseOrder', [Review::class, $purchaseOrder]),
        ]);
    }

    public function complete(PurchaseOrder $purchaseOrder, PurchaseOrderService $service)
    {
        $this->authorize('complete', $purchaseOrder);

        try {
            $service->complete($purchaseOrder, $this->currentUser());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Purchase order marked as completed.');
    }
}
