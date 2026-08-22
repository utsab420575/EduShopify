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

class SavedItemController extends Controller
{
    use InteractsWithBuyerAccount;

    private const TYPES = ['supplier', 'listing', 'rfq', 'quotation'];

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $type = in_array($request->input('type'), self::TYPES, true) ? $request->input('type') : 'supplier';

        $savedItemIds = $account->savedItems()->ofType($type)->latest()->pluck('item_id');

        $items = match ($type) {
            'supplier' => Account::with('supplierProfile.country')->whereIn('id', $savedItemIds)->get(),
            'listing' => Listing::with('supplierAccount.supplierProfile')->whereIn('id', $savedItemIds)->get(),
            'rfq' => Rfq::whereIn('id', $savedItemIds)->get(),
            'quotation' => Quotation::with(['rfq', 'supplierAccount.supplierProfile'])->whereIn('id', $savedItemIds)->get(),
        };

        // Preserve saved-order (most recently saved first) — whereIn() does not.
        $items = $items->sortBy(fn ($item) => array_search($item->id, $savedItemIds->all()))->values();

        return view('backend.buyer.saved-items.index', [
            'type' => $type,
            'items' => $items,
            'counts' => collect(self::TYPES)->mapWithKeys(fn ($t) => [$t => $account->savedItems()->ofType($t)->count()]),
        ]);
    }

    public function toggle(Request $request, SavedItemService $savedItems)
    {
        $request->validate([
            'type' => ['required', 'in:supplier,listing,rfq,quotation'],
            'id' => ['required', 'integer'],
        ]);

        $saved = $savedItems->toggle($this->currentAccount(), $this->currentUser(), $request->string('type'), $request->integer('id'));

        return back()->with('success', $saved ? 'Saved.' : 'Removed from saved items.');
    }
}
