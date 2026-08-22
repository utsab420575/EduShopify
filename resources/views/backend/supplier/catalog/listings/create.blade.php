@extends('backend.layouts.supplier')

@section('title', 'Add Catalog Listing')
@section('breadcrumb', 'Catalog / Add Listing')

@section('body')

    <x-backend.page-header title="Add Listing" subtitle="Create a new product or service listing for the EduShopify education marketplace." />

    <form method="POST" action="{{ route('supplier.catalog.listings.store') }}"
          x-data="{ listingType: 'product', pricingType: 'fixed' }">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">

                <x-backend.form-card title="Listing Type & Basics">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">What are you listing? <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 flex-1"
                                       :class="listingType === 'product' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200'">
                                    <input type="radio" name="listing_type" value="product" x-model="listingType" checked style="accent-color:var(--theme-primary)">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Physical Product / Goods</p>
                                        <p class="text-xs text-gray-400">Lab equipment, stationery, books, furniture, IT hardware</p>
                                    </div>
                                </label>
                                <label class="inline-flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 flex-1"
                                       :class="listingType === 'service' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200'">
                                    <input type="radio" name="listing_type" value="service" x-model="listingType" style="accent-color:var(--theme-primary)">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Educational Service</p>
                                        <p class="text-xs text-gray-400">Software subscriptions, training, maintenance, curriculum</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <x-backend.input name="name" label="Listing Title / Name" required placeholder="e.g. Digital Microscope Pro 4K with LED Stage" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Category <span class="text-red-500">*</span></label>
                                <select name="main_category_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select category</option>
                                    @foreach($categories as $cat)
                                        <optgroup label="{{ $cat->name }}">
                                            <option value="{{ $cat->id }}">{{ $cat->name }} (Main)</option>
                                            @foreach($cat->children as $child)
                                                <option value="{{ $child->id }}">&nbsp;&nbsp;&ndash; {{ $child->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand / Manufacturer (optional)</label>
                                <select name="brand_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <x-backend.input name="sku" label="SKU / Model Number" placeholder="e.g. MIC-4K-2026" />
                        <x-backend.textarea name="short_description" label="Short Summary" placeholder="1-2 sentences summarizing the product features." />
                        <x-backend.textarea name="description" label="Detailed Description" placeholder="Full technical specifications, packaging contents, usage instructions..." />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Pricing & Inventory">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Pricing Model <span class="text-red-500">*</span></label>
                                <select name="pricing_type" x-model="pricingType" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="fixed">Fixed Price</option>
                                    <option value="quote_only">Quote Only</option>
                                    <option value="rfq_enabled">RFQ Enabled</option>
                                </select>
                            </div>
                            <x-backend.input name="currency_code" label="Currency" value="USD" required />
                            <x-backend.input type="number" step="0.01" name="base_price" label="Base Unit Price" placeholder="0.00" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-backend.input type="number" step="0.01" name="compare_at_price" label="Original / Compare Price (Strike-through)" />
                            <x-backend.input type="number" name="min_order_quantity" label="Minimum Order Quantity (MOQ)" value="1" />
                        </div>

                        {{-- Product Specific inventory --}}
                        <div x-show="listingType === 'product'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock Status</label>
                                <select name="stock_status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="limited">Limited</option>
                                    <option value="on_request">On Request</option>
                                </select>
                            </div>
                            <x-backend.input type="number" name="stock_quantity" label="Available Quantity" value="0" />
                            <x-backend.input type="number" name="lead_time_days" label="Lead Time (Days)" placeholder="e.g. 5" />
                        </div>

                        {{-- Service Specific details --}}
                        <div x-show="listingType === 'service'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Service Mode</label>
                                <select name="service_mode" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">Not specified</option>
                                    <option value="onsite">Onsite</option>
                                    <option value="remote">Remote</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                            <x-backend.input type="number" name="delivery_time_days" label="Delivery Time (Days)" placeholder="e.g. 3" />
                        </div>
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Publishing Status">
                    <p class="text-xs text-gray-500 mb-3">After creating your listing, it is saved as a <strong>Draft</strong>. You can add images, variants, and tier pricing on the next screen before submitting it for platform approval.</p>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked style="accent-color:var(--theme-primary)">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Active / Visible in Catalog</label>
                    </div>
                </x-backend.form-card>
            </div>

            <div class="xl:col-span-12 flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
                <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Create Listing &amp; Continue
                </button>
            </div>
        </div>
    </form>

@endsection
