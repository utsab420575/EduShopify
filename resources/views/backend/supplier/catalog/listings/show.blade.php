@extends('backend.layouts.supplier')

@section('title', $listing->name)
@section('breadcrumb', 'Catalog / Listing Details')

@section('body')

    <x-backend.page-header title="{{ $listing->name }}" subtitle="Listing ID: {{ $listing->listing_number }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($listing->approval_status === 'draft' || $listing->approval_status === 'rejected')
                    <form method="POST" action="{{ route('supplier.catalog.listings.submit', $listing) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                        </button>
                    </form>
                @endif
                <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Main Details --}}
        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Overview">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Type</span>
                        <span class="font-semibold text-gray-800 uppercase">{{ $listing->listing_type }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Category</span>
                        <span class="font-semibold text-gray-800">{{ $listing->mainCategory?->name ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Price</span>
                        <span class="font-semibold text-indigo-700 text-sm">{{ $listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'RFQ' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$listing->approval_status" />
                    </div>
                </div>

                @if($listing->short_description)
                    <p class="text-sm text-gray-600 mb-3 font-medium">{{ $listing->short_description }}</p>
                @endif
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $listing->description }}</div>
            </x-backend.form-card>

            {{-- Variants (if product) --}}
            @if($listing->isProduct())
                <x-backend.form-card title="Product Variants">
                    <div class="mb-4">
                        <form method="POST" action="{{ route('supplier.catalog.listings.variants.store', $listing) }}" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            @csrf
                            <p class="text-xs font-semibold text-gray-700 mb-2">Add New Variant (Size, Color, Spec)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-2">
                                <x-backend.input name="name" placeholder="Variant name (e.g. 500ml / Blue)" required />
                                <x-backend.input name="sku" placeholder="SKU" />
                                <x-backend.input type="number" step="0.01" name="price" placeholder="Price" required />
                                <x-backend.input type="number" name="stock_quantity" placeholder="Stock" />
                            </div>
                            <button type="submit" class="btn-primary text-xs font-medium px-3 py-1.5 rounded">
                                <i class="fa-solid fa-plus mr-1"></i> Add Variant
                            </button>
                        </form>
                    </div>

                    @if($listing->variants->isNotEmpty())
                        <div class="overflow-x-auto -mx-5 -mb-5">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 border-y border-gray-100 text-gray-500">
                                    <tr>
                                        <th class="px-5 py-2">Variant</th>
                                        <th class="px-3 py-2">SKU</th>
                                        <th class="px-3 py-2">Price</th>
                                        <th class="px-3 py-2">Stock</th>
                                        <th class="px-5 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($listing->variants as $variant)
                                        <tr>
                                            <td class="px-5 py-2.5 font-medium text-gray-900">{{ $variant->name }}</td>
                                            <td class="px-3 py-2.5 text-gray-500">{{ $variant->sku ?? '-' }}</td>
                                            <td class="px-3 py-2.5 font-semibold text-gray-800">{{ $variant->currency_code }} {{ number_format($variant->price, 2) }}</td>
                                            <td class="px-3 py-2.5">{{ $variant->stock_quantity }}</td>
                                            <td class="px-5 py-2.5 text-right">
                                                <form method="POST" action="{{ route('supplier.catalog.listings.variants.destroy', [$listing, $variant]) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:underline text-[11px]">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-backend.form-card>
            @endif

            {{-- Quantity Break / Tier Pricing --}}
            <x-backend.form-card title="Quantity Break / Tier Pricing">
                <div class="mb-4">
                    <form method="POST" action="{{ route('supplier.catalog.listings.tier-prices.store', $listing) }}" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <p class="text-xs font-semibold text-gray-700 mb-2">Add Volume Discount Tier</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2">
                            <x-backend.input type="number" name="min_quantity" placeholder="Min Qty (e.g. 50)" required />
                            <x-backend.input type="number" name="max_quantity" placeholder="Max Qty (optional)" />
                            <x-backend.input type="number" step="0.01" name="unit_price" placeholder="Tier Unit Price" required />
                        </div>
                        <button type="submit" class="btn-primary text-xs font-medium px-3 py-1.5 rounded">
                            <i class="fa-solid fa-plus mr-1"></i> Add Tier Price
                        </button>
                    </form>
                </div>

                @if($listing->tierPrices->isNotEmpty())
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-y border-gray-100 text-gray-500">
                                <tr>
                                    <th class="px-5 py-2">Quantity Range</th>
                                    <th class="px-3 py-2">Unit Price</th>
                                    <th class="px-5 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($listing->tierPrices as $tp)
                                    <tr>
                                        <td class="px-5 py-2.5 text-gray-800 font-medium">{{ (int)$tp->min_quantity }} &ndash; {{ $tp->max_quantity ? (int)$tp->max_quantity : '∞' }}</td>
                                        <td class="px-3 py-2.5 font-semibold text-indigo-700">{{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}</td>
                                        <td class="px-5 py-2.5 text-right">
                                            <form method="POST" action="{{ route('supplier.catalog.listings.tier-prices.destroy', [$listing, $tp]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline text-[11px]">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-backend.form-card>

        </div>

        {{-- Sidebar Info --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Photos & Media">
                @php($gallery = $listing->getMedia('gallery'))
                @if($gallery->isNotEmpty())
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        @foreach($gallery as $media)
                            <div class="relative group">
                                <img src="{{ $media->getUrl() }}" alt="" class="w-full h-16 object-cover rounded-lg border border-gray-200">
                                <form method="POST" action="{{ route('supplier.catalog.listings.media.destroy', [$listing, $media]) }}" class="absolute top-0.5 right-0.5">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-5 h-5 rounded-full bg-white/90 text-red-600 text-[10px] flex items-center justify-center shadow">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('supplier.catalog.listings.media.store', $listing) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="image" required accept="image/*" class="w-full text-xs text-gray-500">
                    <button type="submit" class="btn-primary w-full text-xs font-medium py-2 rounded">
                        <i class="fa-solid fa-upload mr-1"></i> Upload Image
                    </button>
                </form>
            </x-backend.form-card>

            <x-backend.form-card title="Listing Information">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Created Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Published Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->published_at ? $listing->published_at->format('d M Y') : 'Not published' }}</span>
                    </div>
                    @if($listing->rejection_reason)
                        <div class="p-2 bg-red-50 text-red-700 rounded-lg text-xs mt-2">
                            <strong>Rejection Note:</strong> {{ $listing->rejection_reason }}
                        </div>
                    @endif
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
