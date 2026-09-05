<?php

namespace App\Http\Controllers\Backend\Buyer\SavedItem;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Services\SavedItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedItemController extends Controller
{
    use InteractsWithBuyerAccount;

    private const TYPES = ['supplier', 'listing', 'rfq', 'quotation'];

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $type = in_array($request->input('type'), self::TYPES, true) ? $request->input('type') : 'supplier';

        // Paginate the SavedItem rows themselves (the paginator drives the
        // pagination UI and total count); fetch only that page's models,
        // then re-sort by saved-order since whereIn() does not preserve it.
        $savedItems = $account->savedItems()->ofType($type)->latest()->paginate(12)->withQueryString();
        $savedItemIds = $savedItems->pluck('item_id');

        $items = match ($type) {
            'supplier' => Account::with('supplierProfile.country')->whereIn('id', $savedItemIds)->get(),
            'listing' => Listing::with('supplierAccount.supplierProfile')->whereIn('id', $savedItemIds)->get(),
            'rfq' => Rfq::whereIn('id', $savedItemIds)->get(),
            'quotation' => Quotation::with(['rfq', 'supplierAccount.supplierProfile'])->whereIn('id', $savedItemIds)->get(),
        };

        $items = $items->sortBy(fn ($item) => array_search($item->id, $savedItemIds->all()))->values();

        return view('backend.buyer.saved-items.index', [
            'type' => $type,
            'items' => $items,
            'paginator' => $savedItems,
            'counts' => collect(self::TYPES)->mapWithKeys(fn ($t) => [$t => $account->savedItems()->ofType($t)->count()]),
        ]);
    }

    public function toggle(Request $request, SavedItemService $savedItems)
    {
        $request->validate([
            'type' => ['required', 'in:supplier,listing,rfq,quotation'],
            'id' => ['required', 'integer'],
            'action' => ['nullable', 'in:save,remove,toggle'],
        ]);

        $user = Auth::user();
        $account = $this->currentAccount() ?? $user?->accountMember?->account;

        if (! $account) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'No active account found.'], 403);
            }
            abort(403, 'No active account found.');
        }

        $type = $request->string('type')->toString();
        $id = $request->integer('id');
        $action = $request->input('action', 'toggle');

        $isAlreadySaved = $savedItems->isSaved($account, $type, $id);

        if ($action === 'save') {
            if ($isAlreadySaved) {
                $status = 'already_saved';
                $message = 'Already in saved list';
                $saved = true;
            } else {
                $savedItems->save($account, $user, $type, $id);
                $status = 'saved';
                $message = $type === 'listing' ? 'Product is saved' : ($type === 'supplier' ? 'Supplier is saved' : 'Saved.');
                $saved = true;
            }
        } elseif ($action === 'remove') {
            if ($isAlreadySaved) {
                $savedItems->remove($account, $type, $id);
            }
            $status = 'removed';
            $message = 'Removed from saved items.';
            $saved = false;
        } else {
            $saved = $savedItems->toggle($account, $user, $type, $id);
            $status = $saved ? 'saved' : 'removed';
            $message = $saved
                ? ($type === 'listing' ? 'Product is saved' : ($type === 'supplier' ? 'Supplier is saved' : 'Saved.'))
                : 'Removed from saved items.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => $status,
                'saved' => $saved,
                'message' => $message,
                'type' => $type,
                'id' => $id,
            ]);
        }

        return back()->with('success', $message);
    }
}
