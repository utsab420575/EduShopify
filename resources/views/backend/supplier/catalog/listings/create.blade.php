@extends('backend.layouts.supplier')

@section('title', 'Add Catalog Listing')
@section('breadcrumb', 'Catalog / Add Listing')

@section('body')

    <x-backend.page-header title="Add Listing" subtitle="Create a new product or service listing for the EduShopify education marketplace." />

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-semibold text-red-800">Please correct the following errors:</h4>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('supplier.catalog.listings.store') }}" enctype="multipart/form-data"
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

                        <x-backend.input name="name" label="Listing Title / Name" required placeholder="e.g. Digital Microscope Pro 4K with LED Stage" :value="old('name')" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Category <span class="text-red-500">*</span></label>
                                <select name="main_category_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select category</option>
                                    @foreach($categoryOptions as $opt)
                                        <option value="{{ $opt['id'] }}" @selected(old('main_category_id') == $opt['id'])>
                                            {{ $opt['indent_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand / Manufacturer (optional)</label>
                                <select name="brand_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <x-backend.input name="sku" label="SKU / Model Number" placeholder="e.g. MIC-4K-2026" :value="old('sku')" />
                        <x-backend.textarea name="short_description" label="Short Summary" placeholder="1-2 sentences summarizing the product features." :value="old('short_description')" />
                        <x-backend.textarea name="description" label="Detailed Description" placeholder="Full technical specifications, packaging contents, usage instructions..." :value="old('description')" />
                    </div>
                </x-backend.form-card>

                {{-- Dynamic Specifications & Category Attributes --}}
                <x-backend.form-card title="Category Specifications & Technical Attributes">
                    <p class="text-xs text-gray-500 mb-4">
                        Attributes are automatically loaded based on your selected category and categorized by specification sections. Fields with (<span class="text-red-500 font-bold">*</span>) are mandatory when submitting for platform approval.
                    </p>

                    @include('backend.supplier.catalog.listings.partials.attributes-form', [
                        'initialCategoryId' => old('main_category_id'),
                        'initialValues' => old('attributes', []),
                    ])
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
                            <x-backend.input name="currency_code" label="Currency" :value="old('currency_code', 'USD')" required />
                            <x-backend.input type="number" step="0.01" name="base_price" label="Base Unit Price" placeholder="0.00" :value="old('base_price')" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-backend.input type="number" step="0.01" name="compare_at_price" label="Original / Compare Price (Strike-through)" :value="old('compare_at_price')" />
                            <x-backend.input type="number" name="min_order_quantity" label="Minimum Order Quantity (MOQ)" :value="old('min_order_quantity', 1)" />
                        </div>

                        {{-- Product Specific inventory --}}
                        <div x-show="listingType === 'product'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock Status</label>
                                <select name="stock_status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="in_stock" @selected(old('stock_status') == 'in_stock')>In Stock</option>
                                    <option value="out_of_stock" @selected(old('stock_status') == 'out_of_stock')>Out of Stock</option>
                                    <option value="limited" @selected(old('stock_status') == 'limited')>Limited</option>
                                    <option value="on_request" @selected(old('stock_status') == 'on_request')>On Request</option>
                                </select>
                            </div>
                            <x-backend.input type="number" name="stock_quantity" label="Available Quantity" :value="old('stock_quantity', 0)" />
                            <x-backend.input type="number" name="lead_time_days" label="Lead Time (Days)" placeholder="e.g. 5" :value="old('lead_time_days')" />
                        </div>

                        {{-- Service Specific details --}}
                        <div x-show="listingType === 'service'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Service Mode</label>
                                <select name="service_mode" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">Not specified</option>
                                    <option value="onsite" @selected(old('service_mode') == 'onsite')>Onsite</option>
                                    <option value="remote" @selected(old('service_mode') == 'remote')>Remote</option>
                                    <option value="hybrid" @selected(old('service_mode') == 'hybrid')>Hybrid</option>
                                </select>
                            </div>
                            <x-backend.input type="number" name="delivery_time_days" label="Delivery Time (Days)" placeholder="e.g. 3" :value="old('delivery_time_days')" />
                        </div>
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                {{-- Product Images & Media --}}
                <x-backend.form-card title="Product Gallery & Media">
                    <p class="text-xs text-gray-500 mb-3">Upload high-resolution images of your product (PNG, JPG up to 5MB).</p>
                    <div class="space-y-3">
                        <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Publishing Status">
                    <p class="text-xs text-gray-500 mb-3">You can save your listing as a <strong>Draft</strong> to complete later, or <strong>Submit for Approval</strong> when all required specifications and details are filled.</p>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked style="accent-color:var(--theme-primary)">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Active / Visible in Catalog</label>
                    </div>
                </x-backend.form-card>
            </div>

            <div class="xl:col-span-12 flex items-center justify-end gap-3 bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <button type="submit" name="action" value="save_draft" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save as Draft
                </button>
                <button type="submit" name="action" value="submit_approval" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                </button>
            </div>
        </div>
    </form>

@endsection

