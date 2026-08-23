<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingTierPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TierPriceController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'listing_variant_id' => ['nullable', 'exists:listing_variants,id'],
            'min_quantity'       => ['required', 'numeric', 'min:1'],
            'max_quantity'       => ['nullable', 'numeric', 'gt:min_quantity'],
            'unit_price'         => ['required', 'numeric', 'min:0'],
        ]);

        if (!empty($validated['listing_variant_id'])) {
            $listing->variants()->findOrFail($validated['listing_variant_id']);
        }

        $tierPrice = ListingTierPrice::create([
            'listing_id'         => $listing->id,
            'listing_variant_id' => $validated['listing_variant_id'] ?? null,
            'min_quantity'       => $validated['min_quantity'],
            'max_quantity'       => $validated['max_quantity'] ?? null,
            'unit_price'         => $validated['unit_price'],
            'currency_code'      => $listing->currency_code,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Tier pricing added successfully.',
                'tierPrice' => $tierPrice,
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Tier pricing added.');
    }

    public function copyGlobal(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'target_variant_id' => ['required', 'exists:listing_variants,id'],
        ]);

        $variant = $listing->variants()->findOrFail($validated['target_variant_id']);
        $globalTiers = $listing->allTierPrices()->whereNull('listing_variant_id')->get();

        if ($globalTiers->isEmpty()) {
            return back()->with('error', 'No global tier prices found to copy.');
        }

        DB::transaction(function () use ($listing, $variant, $globalTiers) {
            $variant->tierPrices()->delete();

            foreach ($globalTiers as $gt) {
                ListingTierPrice::create([
                    'listing_id'         => $listing->id,
                    'listing_variant_id' => $variant->id,
                    'min_quantity'       => $gt->min_quantity,
                    'max_quantity'       => $gt->max_quantity,
                    'unit_price'         => $gt->unit_price,
                    'currency_code'      => $gt->currency_code,
                ]);
            }
        });

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', "Global tier prices copied to {$variant->name}.");
    }

    public function update(Request $request, Listing $listing, ListingTierPrice $tierPrice)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $tierPrice->listing_id !== $listing->id, 403);

        $validated = $request->validate([
            'min_quantity' => ['required', 'numeric', 'min:1'],
            'max_quantity' => ['nullable', 'numeric', 'gt:min_quantity'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
        ]);

        $tierPrice->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Tier pricing updated.',
                'tierPrice' => $tierPrice,
            ]);
        }

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
