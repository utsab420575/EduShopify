<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingVariant;
use App\Models\ListingVariantAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'attributes' => ['nullable', 'array'],
        ]);

        $variant = DB::transaction(function () use ($listing, $validated, $request) {
            $variant = ListingVariant::create([
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
                'sort_order' => (int) $listing->variants()->max('sort_order') + 1,
            ]);

            if ($request->filled('attributes') && is_array($request->input('attributes'))) {
                foreach ($request->input('attributes') as $attrId => $val) {
                    if ($val === null || $val === '') continue;
                    ListingVariantAttribute::create([
                        'listing_variant_id' => $variant->id,
                        'attribute_id'       => (int) $attrId,
                        'attribute_value_id' => is_numeric($val) ? (int) $val : null,
                        'custom_value'       => !is_numeric($val) ? (string) $val : null,
                    ]);
                }
            }

            return $variant;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Variant added successfully.',
                'variant' => $variant->load('variantAttributes.attribute', 'variantAttributes.attributeValue'),
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant added.');
    }

    public function bulkGenerate(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $request->validate([
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
        ]);

        $createdCount = DB::transaction(function () use ($listing, $request) {
            $currentSort = (int) $listing->variants()->max('sort_order');
            $count = 0;

            foreach ($request->input('variants') as $item) {
                if (empty($item['name'])) continue;

                $currentSort++;
                $variant = ListingVariant::create([
                    'listing_id'       => $listing->id,
                    'name'             => $item['name'],
                    'sku'              => $item['sku'] ?? null,
                    'price'            => $item['price'] ?? $listing->base_price ?? 0,
                    'compare_at_price' => $item['compare_at_price'] ?? null,
                    'currency_code'    => $listing->currency_code,
                    'stock_quantity'   => $item['stock_quantity'] ?? 0,
                    'stock_status'     => $item['stock_status'] ?? 'in_stock',
                    'min_order_quantity' => $item['min_order_quantity'] ?? 1,
                    'is_active'        => true,
                    'sort_order'       => $currentSort,
                ]);

                if (!empty($item['attributes']) && is_array($item['attributes'])) {
                    foreach ($item['attributes'] as $attrId => $attrVal) {
                        if ($attrVal === null || $attrVal === '') continue;
                        ListingVariantAttribute::create([
                            'listing_variant_id' => $variant->id,
                            'attribute_id'       => (int) $attrId,
                            'attribute_value_id' => is_numeric($attrVal) ? (int) $attrVal : null,
                            'custom_value'       => !is_numeric($attrVal) ? (string) $attrVal : null,
                        ]);
                    }
                }
                $count++;
            }

            return $count;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$createdCount} variant(s) created successfully.",
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', "{$createdCount} variant(s) generated successfully.");
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
            'is_active' => ['nullable', 'boolean'],
        ]);

        $variant->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Variant updated.',
                'variant' => $variant,
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant updated.');
    }

    public function destroy(Listing $listing, ListingVariant $variant)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id || $variant->listing_id !== $listing->id, 403);

        $variant->variantAttributes()->delete();
        $variant->delete();

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Variant removed.');
    }
}
