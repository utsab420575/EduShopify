<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingVariant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,on_backorder,made_to_order'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1'],
        ]);

        ListingVariant::create([
            'listing_id' => $listing->id,
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'currency_code' => $listing->currency_code,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'stock_status' => $validated['stock_status'] ?? 'in_stock',
            'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
            'is_active' => true,
            'sort_order' => $listing->variants()->max('sort_order') + 1,
        ]);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant added.');
    }

    public function update(Request $request, Listing $listing, ListingVariant $variant)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $variant->listing_id !== $listing->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,on_backorder,made_to_order'],
        ]);

        $variant->update($validated);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant updated.');
    }

    public function destroy(Listing $listing, ListingVariant $variant)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $variant->listing_id !== $listing->id, 403);

        $variant->delete();

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant removed.');
    }
}
