<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingTierPrice;
use Illuminate\Http\Request;

class TierPriceController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'min_quantity' => ['required', 'numeric', 'min:1'],
            'max_quantity' => ['nullable', 'numeric', 'gt:min_quantity'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        ListingTierPrice::create([
            'listing_id' => $listing->id,
            'min_quantity' => $validated['min_quantity'],
            'max_quantity' => $validated['max_quantity'] ?? null,
            'unit_price' => $validated['unit_price'],
            'currency_code' => $listing->currency_code,
        ]);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Tier pricing added.');
    }

    public function update(Request $request, Listing $listing, ListingTierPrice $tierPrice)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $tierPrice->listing_id !== $listing->id, 403);

        $validated = $request->validate([
            'min_quantity' => ['required', 'numeric', 'min:1'],
            'max_quantity' => ['nullable', 'numeric', 'gt:min_quantity'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $tierPrice->update($validated);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Tier pricing updated.');
    }

    public function destroy(Listing $listing, ListingTierPrice $tierPrice)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $tierPrice->listing_id !== $listing->id, 403);

        $tierPrice->delete();

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Tier price removed.');
    }
}
