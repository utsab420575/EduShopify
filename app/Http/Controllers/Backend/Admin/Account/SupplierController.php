<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $accounts = Account::where('is_system_account', false)
            ->whereHas('capabilities', fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier')))
            ->when($request->filled('search'), fn ($q) => $q->where('display_name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->whereHas('capabilities', fn ($q2) => $q2->whereHas('capabilityType', fn ($q3) => $q3->where('code', 'supplier'))->where('status', $request->string('status')));
            })
            ->when($request->filled('document_status'), function ($q) use ($request) {
                $q->whereHas('supplierDocuments', fn ($q2) => $q2->where('status', $request->string('document_status')));
            })
            ->with(['supplierProfile', 'activeSubscription.plan', 'capabilities' => fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier'))])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.suppliers.index', [
            'accounts' => $accounts,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Account $account)
    {
        $this->authorize('platform.accounts.view');

        abort_unless($account->hasCapability('supplier'), 404);

        $account->load(['supplierProfile.country', 'supplierTypes', 'locations', 'activeSubscription.plan']);
        $supplierCapability = $account->capabilities()->whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->with('applicationHistory')->first();

        return view('backend.admin.suppliers.show', [
            'account' => $account,
            'capability' => $supplierCapability,
            'documents' => $account->supplierDocuments()->with('documentType')->current()->orderByDesc('created_at')->get(),
            'listingCount' => $account->listings()->count(),
            'publishedListingCount' => $account->listings()->where('approval_status', 'approved')->count(),
            'quotationCount' => $account->quotations()->count(),
            'awardCount' => $account->supplierAwards()->count(),
            'poCount' => $account->supplierPurchaseOrders()->count(),
            'reviewCount' => $account->receivedReviews()->count(),
        ]);
    }
}
