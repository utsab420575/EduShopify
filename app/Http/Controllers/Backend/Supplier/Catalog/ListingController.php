<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ProductDetail;
use App\Models\ServiceDetail;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->listings()->with(['mainCategory', 'brand', 'unit'])->latest();

        if ($request->filled('status')) {
            $query->where('approval_status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $query->where('listing_type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('listing_number', 'like', $s)
                  ->orWhere('sku', 'like', $s);
            });
        }

        $listings = $query->paginate(12)->withQueryString();

        return view('backend.supplier.catalog.listings.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'listings' => $listings,
            'status' => $request->string('status')->toString(),
            'type' => $request->string('type')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $account = $this->currentAccount();
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
        $brands = Brand::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('backend.supplier.catalog.listings.create', [
            'account' => $account,
            'user' => $this->currentUser(),
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'listing_type' => ['required', 'in:product,service'],
            'name' => ['required', 'string', 'max:255'],
            'main_category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:fixed,quote_only,rfq_enabled'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'max:3'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
            // Product details
            'product_type' => ['nullable', 'in:simple,variable'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:500'],
            // Service details
            'service_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'delivery_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $listingNumber = 'LST-' . strtoupper(Str::random(8));

        $listing = Listing::create([
            'supplier_account_id' => $account->id,
            'created_by_user_id' => $this->currentUser()->id,
            'listing_type' => $validated['listing_type'],
            'listing_number' => $listingNumber,
            'main_category_id' => $validated['main_category_id'],
            'brand_id' => $validated['brand_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . strtolower(Str::random(5)),
            'sku' => $validated['sku'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'pricing_type' => $validated['pricing_type'],
            'base_price' => $validated['base_price'] ?? null,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'currency_code' => $validated['currency_code'],
            'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
            'unit_id' => $validated['unit_id'] ?? null,
            'approval_status' => 'draft',
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($validated['listing_type'] === 'product') {
            ProductDetail::create([
                'listing_id' => $listing->id,
                'product_type' => $validated['product_type'] ?? 'simple',
                'stock_status' => $validated['stock_status'] ?? 'on_request',
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'lead_time_days' => $validated['lead_time_days'] ?? null,
                'warranty_terms' => $validated['warranty_terms'] ?? null,
            ]);
        } else {
            ServiceDetail::create([
                'listing_id' => $listing->id,
                'service_mode' => $validated['service_mode'] ?? null,
                'delivery_time_days' => $validated['delivery_time_days'] ?? null,
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Listing created successfully.');
    }

    public function show(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->load(['mainCategory', 'brand', 'unit', 'productDetail', 'serviceDetail', 'variants', 'tierPrices']);

        return view('backend.supplier.catalog.listings.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'listing' => $listing,
        ]);
    }

    public function edit(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->load(['productDetail', 'serviceDetail']);
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
        $brands = Brand::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('backend.supplier.catalog.listings.edit', [
            'account' => $account,
            'user' => $this->currentUser(),
            'listing' => $listing,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'main_category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:fixed,quote_only,rfq_enabled'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'max:3'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
            // Product details
            'product_type' => ['nullable', 'in:simple,variable'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:500'],
            // Service details
            'service_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'delivery_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $listing->update([
            'main_category_id' => $validated['main_category_id'],
            'brand_id' => $validated['brand_id'] ?? null,
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'pricing_type' => $validated['pricing_type'],
            'base_price' => $validated['base_price'] ?? null,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'currency_code' => $validated['currency_code'],
            'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
            'unit_id' => $validated['unit_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($listing->isProduct() && $listing->productDetail) {
            $listing->productDetail->update([
                'product_type' => $validated['product_type'] ?? 'simple',
                'stock_status' => $validated['stock_status'] ?? 'on_request',
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'lead_time_days' => $validated['lead_time_days'] ?? null,
                'warranty_terms' => $validated['warranty_terms'] ?? null,
            ]);
        } elseif ($listing->isService() && $listing->serviceDetail) {
            $listing->serviceDetail->update([
                'service_mode' => $validated['service_mode'] ?? null,
                'delivery_time_days' => $validated['delivery_time_days'] ?? null,
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Listing updated successfully.');
    }

    public function submit(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->update([
            'approval_status' => 'pending',
        ]);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Listing submitted for platform approval.');
    }

    public function destroy(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->delete();

        return redirect()->route('supplier.catalog.listings.index')->with('success', 'Listing deleted.');
    }
}
