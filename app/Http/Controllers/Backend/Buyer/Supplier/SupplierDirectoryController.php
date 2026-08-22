<?php

namespace App\Http\Controllers\Backend\Buyer\Supplier;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Country;
use App\Models\SupplierType;
use App\Services\MessagingService;
use App\Services\SavedItemService;
use Illuminate\Http\Request;

class SupplierDirectoryController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request, SavedItemService $savedItems)
    {
        $account = $this->currentAccount();

        $suppliers = Account::marketplace()
            ->whereHas('capabilities', fn ($q) => $q->where('status', 'active')->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier')))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('supplierProfile', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('type'), fn ($q) => $q->whereHas('supplierTypes', fn ($q2) => $q2->where('supplier_types.id', $request->integer('type'))))
            ->when($request->filled('country'), fn ($q) => $q->whereHas('supplierProfile', fn ($q2) => $q2->where('country_id', $request->integer('country'))))
            ->with(['supplierProfile.country', 'supplierTypes'])
            ->paginate(12)
            ->withQueryString();

        $savedIds = $account->savedItems()->ofType('supplier')->pluck('item_id');

        return view('backend.buyer.suppliers.index', [
            'suppliers' => $suppliers,
            'savedIds' => $savedIds,
            'search' => $request->string('search')->toString(),
            'type' => $request->integer('type'),
            'country' => $request->integer('country'),
            'supplierTypes' => SupplierType::orderBy('name')->get(['id', 'name']),
            'countries' => Country::active()->get(['id', 'name']),
        ]);
    }

    public function show(Account $supplierAccount)
    {
        abort_unless($supplierAccount->hasActiveCapability('supplier'), 404);

        $account = $this->currentAccount();

        $supplierAccount->load(['supplierProfile.country', 'supplierProfile.state', 'supplierProfile.city', 'supplierTypes']);

        $listings = $supplierAccount->listings()->published()->latest()->limit(6)->get();
        $reviews = $supplierAccount->receivedReviews()->published()->with('createdBy')->latest()->limit(10)->get();

        return view('backend.buyer.suppliers.show', [
            'supplierAccount' => $supplierAccount,
            'listings' => $listings,
            'reviews' => $reviews,
            'isSaved' => $account->savedItems()->ofType('supplier')->where('item_id', $supplierAccount->id)->exists(),
        ]);
    }

    public function toggleSave(Account $supplierAccount, SavedItemService $savedItems)
    {
        $saved = $savedItems->toggle($this->currentAccount(), $this->currentUser(), 'supplier', $supplierAccount->id);

        return back()->with('success', $saved ? 'Supplier saved.' : 'Removed from saved suppliers.');
    }

    public function message(Account $supplierAccount, MessagingService $messaging)
    {
        $this->authorize('start', \App\Models\Conversation::class);

        $conversation = $messaging->startOrGetConversation(
            $this->currentAccount(), $this->currentUser(), $supplierAccount, 'general'
        );

        return redirect()->route('buyer.messages.show', $conversation);
    }
}
