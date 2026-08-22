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
        $account = $this->currentAccount();
        abort_if($award->supplier_account_id !== $account->id, 403);

        $award->load(['rfq.buyerAccount.buyerProfile', 'quotation.items', 'purchaseOrder']);

        return view('backend.supplier.procurement.awards.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'award' => $award,
        ]);
    }

    public function accept(Request $request, Award $award, AwardResponseService $service)
    {
        $account = $this->currentAccount();
        abort_if($award->supplier_account_id !== $account->id, 403);

        $note = $request->input('note');
        $service->accept($award, $note);

        return redirect()->route('supplier.awards.show', $award)->with('success', 'Award accepted! Purchase Order has been generated.');
    }

    public function reject(Request $request, Award $award, AwardResponseService $service)
    {
        $account = $this->currentAccount();
        abort_if($award->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $service->reject($award, $validated['reason']);

        return redirect()->route('supplier.awards.show', $award)->with('success', 'Award rejected.');
    }
}
