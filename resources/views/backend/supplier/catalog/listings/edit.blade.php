@extends('backend.layouts.supplier')

@section('title', 'Edit Listing — ' . $listing->name)
@section('breadcrumb', 'Catalog / Edit Listing')

@section('body')

    <x-backend.page-header title="Edit Listing" subtitle="{{ $listing->listing_number }} — {{ $listing->name }}" />

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

    <form method="POST" action="{{ route('supplier.catalog.listings.update', $listing) }}" enctype="multipart/form-data"
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
                                    <option value="">Select category</option>
                                    @foreach($categoryOptions as $opt)
                                        <option value="{{ $opt['id'] }}" @selected(old('main_category_id', $listing->main_category_id) == $opt['id'])>
                                            {{ $opt['indent_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                                <select name="brand_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">None</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(old('brand_id', $listing->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <x-backend.input name="sku" label="SKU / Model Number" :value="old('sku', $listing->sku)" />
                        <x-backend.textarea name="short_description" label="Short Summary" :value="old('short_description', $listing->short_description)" />
                        <x-backend.textarea name="description" label="Detailed Description" :value="old('description', $listing->description)" />
                    </div>
                </x-backend.form-card>

                {{-- Dynamic Category Specifications --}}
                <x-backend.form-card title="Category Specifications & Technical Attributes">
                    <p class="text-xs text-gray-500 mb-4">
                        Attributes are automatically loaded based on your selected category. Changing the category will retain common specifications and prompt before discarding invalid ones.
                    </p>

                    @include('backend.supplier.catalog.listings.partials.attributes-form', [
                        'initialCategoryId' => old('main_category_id', $listing->main_category_id),
                        'initialValues' => old('attributes', $existingValues),
                    ])
                </x-backend.form-card>

                <x-backend.form-card title="Pricing & Inventory">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Pricing Model <span class="text-red-500">*</span></label>
                                <select name="pricing_type" x-model="pricingType" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="fixed" @selected(old('pricing_type', $listing->pricing_type) === 'fixed')>Fixed Price</option>
                                    <option value="quote_only" @selected(old('pricing_type', $listing->pricing_type) === 'quote_only')>Quote Only</option>
                                    <option value="rfq_enabled" @selected(old('pricing_type', $listing->pricing_type) === 'rfq_enabled')>RFQ Enabled</option>
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
                                        <option value="in_stock" @selected(old('stock_status', $listing->productDetail->stock_status) === 'in_stock')>In Stock</option>
                                        <option value="out_of_stock" @selected(old('stock_status', $listing->productDetail->stock_status) === 'out_of_stock')>Out of Stock</option>
                                        <option value="limited" @selected(old('stock_status', $listing->productDetail->stock_status) === 'limited')>Limited</option>
                                        <option value="on_request" @selected(old('stock_status', $listing->productDetail->stock_status) === 'on_request')>On Request</option>
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
                                        <option value="onsite" @selected(old('service_mode', $listing->serviceDetail->service_mode) === 'onsite')>Onsite</option>
                                        <option value="remote" @selected(old('service_mode', $listing->serviceDetail->service_mode) === 'remote')>Remote</option>
                                        <option value="hybrid" @selected(old('service_mode', $listing->serviceDetail->service_mode) === 'hybrid')>Hybrid</option>
                                    </select>
                                </div>
                                <x-backend.input type="number" name="delivery_time_days" label="Delivery Time (Days)" :value="old('delivery_time_days', $listing->serviceDetail->delivery_time_days)" />
                            </div>
                        @endif
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                {{-- Images & Media --}}
                <x-backend.form-card title="Upload Additional Images">
                    <p class="text-xs text-gray-500 mb-3">Add new product gallery photos (PNG, JPG up to 5MB).</p>
                    <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    
                    @if($listing->getMedia('gallery')->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Current Gallery Images</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($listing->getMedia('gallery') as $media)
                                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square">
                                        <img src="{{ $media->getUrl() }}" alt="{{ $listing->name }}" class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-backend.form-card>

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

            <div class="xl:col-span-12 flex items-center justify-end gap-3 bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <button type="submit" name="action" value="save_draft" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Update Listing
                </button>
                @if($listing->approval_status === 'draft' || $listing->approval_status === 'rejected')
                    <button type="submit" name="action" value="submit_approval" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Update &amp; Submit for Approval
                    </button>
                @endif
            </div>
        </div>
    </form>

@endsection

