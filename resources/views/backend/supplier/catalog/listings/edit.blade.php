@extends('backend.layouts.supplier')

@section('title', 'Edit Listing — ' . $listing->name)
@section('breadcrumb', 'Catalog / Edit Listing')

@section('body')

    <x-backend.page-header title="Edit Listing" subtitle="{{ $listing->listing_number }} — {{ $listing->name }}" />

    <form method="POST" action="{{ route('supplier.catalog.listings.update', $listing) }}"
          x-data="{ listingType: '{{ $listing->listing_type }}', pricingType: '{{ $listing->pricing_type }}' }">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">

                <x-backend.form-card title="Listing Information">
                    <div class="space-y-4">
                        <x-backend.input name="name" label="Listing Title / Name" required :value="old('name', $listing->name)" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Category <span class="text-red-500">*</span></label>
                                <select name="main_category_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($categories as $cat)
                                        <optgroup label="{{ $cat->name }}">
                                            <option value="{{ $cat->id }}" @selected($listing->main_category_id == $cat->id)>{{ $cat->name }}</option>
                                            @foreach($cat->children as $child)
                                                <option value="{{ $child->id }}" @selected($listing->main_category_id == $child->id)>&nbsp;&nbsp;&ndash; {{ $child->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                                <select name="brand_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">None</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected($listing->brand_id == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <x-backend.input name="sku" label="SKU / Model Number" :value="old('sku', $listing->sku)" />
                        <x-backend.textarea name="short_description" label="Short Summary" :value="old('short_description', $listing->short_description)" />
                        <x-backend.textarea name="description" label="Detailed Description" :value="old('description', $listing->description)" />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Pricing & Inventory">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Pricing Model <span class="text-red-500">*</span></label>
                                <select name="pricing_type" x-model="pricingType" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="fixed" @selected($listing->pricing_type === 'fixed')>Fixed Price</option>
                                    <option value="quote_only" @selected($listing->pricing_type === 'quote_only')>Quote Only</option>
                                    <option value="rfq_enabled" @selected($listing->pricing_type === 'rfq_enabled')>RFQ Enabled</option>
                                </select>
                            </div>
                            <x-backend.input name="currency_code" label="Currency" :value="old('currency_code', $listing->currency_code)" required />
                            <x-backend.input type="number" step="0.01" name="base_price" label="Base Price" :value="old('base_price', $listing->base_price)" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-backend.input type="number" step="0.01" name="compare_at_price" label="Compare Price" :value="old('compare_at_price', $listing->compare_at_price)" />
                            <x-backend.input type="number" name="min_order_quantity" label="MOQ" :value="old('min_order_quantity', $listing->min_order_quantity)" />
                        </div>

                        @if($listing->isProduct() && $listing->productDetail)
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock Status</label>
                                    <select name="stock_status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                        <option value="in_stock" @selected($listing->productDetail->stock_status === 'in_stock')>In Stock</option>
                                        <option value="out_of_stock" @selected($listing->productDetail->stock_status === 'out_of_stock')>Out of Stock</option>
                                        <option value="limited" @selected($listing->productDetail->stock_status === 'limited')>Limited</option>
                                        <option value="on_request" @selected($listing->productDetail->stock_status === 'on_request')>On Request</option>
                                    </select>
                                </div>
                                <x-backend.input type="number" name="stock_quantity" label="Stock Quantity" :value="old('stock_quantity', $listing->productDetail->stock_quantity)" />
                                <x-backend.input type="number" name="lead_time_days" label="Lead Time (Days)" :value="old('lead_time_days', $listing->productDetail->lead_time_days)" />
                            </div>
                        @elseif($listing->isService() && $listing->serviceDetail)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Service Mode</label>
                                    <select name="service_mode" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                        <option value="">Not specified</option>
                                        <option value="onsite" @selected($listing->serviceDetail->service_mode === 'onsite')>Onsite</option>
                                        <option value="remote" @selected($listing->serviceDetail->service_mode === 'remote')>Remote</option>
                                        <option value="hybrid" @selected($listing->serviceDetail->service_mode === 'hybrid')>Hybrid</option>
                                    </select>
                                </div>
                                <x-backend.input type="number" name="delivery_time_days" label="Delivery Time (Days)" :value="old('delivery_time_days', $listing->serviceDetail->delivery_time_days)" />
                            </div>
                        @endif
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Listing Status">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Approval Status</span>
                            <x-backend.status-badge :status="$listing->approval_status" />
                        </div>
                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" @checked($listing->is_active) style="accent-color:var(--theme-primary)">
                            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                        </div>
                    </div>
                </x-backend.form-card>
            </div>

            <div class="xl:col-span-12 flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
                <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Update Listing
                </button>
            </div>
        </div>
    </form>

@endsection
