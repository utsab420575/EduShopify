<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Award;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Award::class);

        $account = $this->currentAccount();

        $awards = $account->buyerAwards()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['rfq', 'supplierAccount.supplierProfile', 'purchaseOrder'])
            ->latest('awarded_at')
            ->paginate(10)
            ->withQueryString();

        return view('backend.buyer.procurement.awards.index', [
            'awards' => $awards,
            'status' => $request->string('status')->toString(),
            'statusOptions' => [
                'pending_supplier_response' => 'Pending Response', 'accepted' => 'Accepted',
                'rejected_by_supplier' => 'Rejected', 'cancelled' => 'Cancelled', 'superseded' => 'Superseded',
            ],
        ]);
    }

    public function show(Award $award)
    {
        $this->authorize('view', $award);

        $award->load(['rfq', 'quotation.items', 'supplierAccount.supplierProfile', 'purchaseOrder']);

        return view('backend.buyer.procurement.awards.show', ['award' => $award]);
    }
}
