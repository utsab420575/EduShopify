<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Services\AwardResponseService;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->supplierAwards()->with(['rfq', 'buyerAccount.buyerProfile', 'quotation'])->latest('awarded_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $awards = $query->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.awards.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'awards' => $awards,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Award $award)
    {
        $this->authorize('view', $award);

        $award->load(['rfq.buyerAccount.buyerProfile', 'quotation.items', 'purchaseOrder']);

        return view('backend.supplier.procurement.awards.show', [
            'account' => $this->currentAccount(),
            'user' => $this->currentUser(),
            'award' => $award,
        ]);
    }

    public function accept(Request $request, Award $award, AwardResponseService $service)
    {
        $this->authorize('accept', $award);

        $service->accept($award, $request->input('note'));

        return redirect()->route('supplier.awards.show', $award)->with('success', 'Award accepted! Purchase Order has been generated.');
    }

    public function reject(Request $request, Award $award, AwardResponseService $service)
    {
        $this->authorize('reject', $award);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $service->reject($award, $validated['reason']);

        return redirect()->route('supplier.awards.show', $award)->with('success', 'Award rejected.');
    }
}
