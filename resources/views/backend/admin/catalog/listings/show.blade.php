@extends('backend.layouts.admin')

@section('title', $listing->name . ' — Listing Review')
@section('breadcrumb', 'Catalog & Taxonomy / Listings / ' . $listing->name)

@section('body')

    {{-- Page Header --}}
    <x-backend.page-header :title="$listing->name" :subtitle="'Listing #' . ($listing->sku ?? $listing->id)">
        <x-slot:actions>
            <div class="flex items-center gap-2 flex-wrap">
                <span @class([
                    'px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider',
                    'bg-amber-100 text-amber-800' => $listing->approval_status === 'pending',
                    'bg-emerald-100 text-emerald-800' => $listing->approval_status === 'approved',
                    'bg-rose-100 text-rose-800' => $listing->approval_status === 'rejected',
                ])>
                    <i class="fa-solid {{ $listing->approval_status === 'approved' ? 'fa-circle-check' : ($listing->approval_status === 'pending' ? 'fa-clock' : 'fa-circle-xmark') }} mr-1"></i>
                    {{ ucfirst($listing->approval_status) }}
                </span>

                <span @class([
                    'px-2.5 py-1 rounded-full text-xs font-semibold',
                    'bg-blue-100 text-blue-800' => $listing->is_active,
                    'bg-gray-100 text-gray-600' => !$listing->is_active,
                ])>
                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                </span>

                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 uppercase">
                    {{ $listing->listing_type }}
                </span>

                @if($listing->is_featured)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        <i class="fa-solid fa-star text-amber-500 mr-1"></i> Featured
                    </span>
                @endif
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Top Moderation Action Toolbar --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-gray-500">Moderation Actions:</span>

            @if($listing->approval_status === 'pending')
                <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'This will approve the listing and publish it immediately to the public marketplace.', 'question', 'Yes, Approve & Publish')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-check"></i> Approve &amp; Publish
                    </button>
                </form>
                <button type="button" @click="$dispatch('open-modal-reject')" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-ban"></i> Reject Listing
                </button>
            @elseif($listing->approval_status === 'approved')
                {{-- Undo Approval (Revert back to Pending) --}}
                <form method="POST" action="{{ route('admin.catalog.listings.undo-approve', $listing) }}" onsubmit="return confirmSwal(this, 'Revert Approval to Pending?', 'This will revoke approval and return the listing to Pending Review. It will be hidden from the public marketplace until re-approved.', 'warning', 'Yes, Revert to Pending')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors" title="Revoke approval and return to pending review">
                        <i class="fa-solid fa-rotate-left"></i> Undo Approval
                    </button>
                </form>

                @if($listing->is_active)
                    <button type="button" @click="$dispatch('open-modal-deactivate')" class="px-4 py-2 rounded-lg border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-pause"></i> Suspend / Deactivate
                    </button>
                @else
                    <form method="POST" action="{{ route('admin.catalog.listings.reactivate', $listing) }}" onsubmit="return confirmSwal(this, 'Reactivate Listing?', 'This will reactivate the listing on the marketplace.', 'question', 'Yes, Reactivate')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-play"></i> Reactivate Listing
                        </button>
                    </form>
                @endif
            @elseif($listing->approval_status === 'rejected')
                <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve Previously Rejected Listing?', 'This will approve the listing and publish it to the marketplace.', 'question', 'Yes, Approve & Publish')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-check"></i> Re-Approve &amp; Publish
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.catalog.listings.feature', $listing) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-star {{ $listing->is_featured ? 'text-amber-500' : 'text-gray-400' }}"></i>
                    {{ $listing->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                </button>
            </form>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('frontend.listings.show', $listing) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> View on Marketplace
            </a>
            <a href="{{ route('admin.approvals.index', ['tab' => 'listings']) }}" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-list-check text-gray-400"></i> Approval Queue
            </a>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left 8 Columns: Full Listing Review Content --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. Photo & Media Gallery --}}
            <x-backend.form-card title="Photos & Media Gallery">
                @php($gallery = $listing->getMedia('gallery'))
                @if($gallery->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" x-data="{ activePreview: '{{ $gallery->first()->getUrl() }}' }">
                        @foreach($gallery as $idx => $media)
                            <div class="group relative aspect-square rounded-xl border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-indigo-500 transition-all"
                                 @click="activePreview = '{{ $media->getUrl() }}'">
                                <img src="{{ $media->getUrl() }}" alt="{{ $listing->name }}" class="w-full h-full object-contain p-1">
                                @if($idx === 0)
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 bg-indigo-600/90 text-white text-[10px] font-bold rounded-md shadow-xs">Primary</span>
                                @endif
                                <a href="{{ $media->getUrl() }}" target="_blank" @click.stop class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-full bg-white/90 text-gray-700 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-xs">
                                    <i class="fa-solid fa-expand text-[10px]"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <i class="fa-solid fa-image text-3xl mb-1.5"></i>
                        <p class="text-xs">No media uploaded for this listing.</p>
                    </div>
                @endif
            </x-backend.form-card>

            {{-- 2. Commercial & Listing Overview --}}
            <x-backend.form-card title="Commercial Terms & Product Information">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">Primary Category</dt>
                        <dd class="font-bold text-gray-900">{{ $listing->mainCategory?->name ?? '—' }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">Brand</dt>
                        <dd class="font-bold text-gray-900">{{ $listing->brand?->name ?? 'Unbranded / Generic' }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">SKU / Model Number</dt>
                        <dd class="font-bold font-mono text-gray-900">{{ $listing->sku ?? '—' }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">Base Price</dt>
                        <dd class="font-bold text-indigo-700 text-sm">
                            {{ $listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'Negotiable / Quote' }}
                        </dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">Pricing Type</dt>
                        <dd class="font-bold text-gray-900">{{ ucfirst($listing->pricing_type ?? 'Fixed') }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-gray-500 font-medium mb-0.5">Minimum Order Quantity (MOQ)</dt>
                        <dd class="font-bold text-gray-900">{{ $listing->min_order_quantity ? (int)$listing->min_order_quantity . ' ' . ($listing->unit?->symbol ?? 'units') : '1 unit' }}</dd>
                    </div>
                </dl>

                {{-- Product Detail Specifics --}}
                @if($listing->productDetail)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Product Logistics & Policies</h4>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Product Type</dt>
                                <dd class="font-semibold text-gray-800">{{ ucfirst($listing->productDetail->product_type ?? 'Simple') }}</dd>
                            </div>
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Stock Status</dt>
                                <dd class="font-semibold text-gray-800">{{ str_replace('_', ' ', ucfirst($listing->productDetail->stock_status ?? 'In Stock')) }}</dd>
                            </div>
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Lead Time</dt>
                                <dd class="font-semibold text-gray-800">{{ $listing->productDetail->lead_time_days ? $listing->productDetail->lead_time_days . ' days' : 'Immediate' }}</dd>
                            </div>
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Country of Origin</dt>
                                <dd class="font-semibold text-gray-800">{{ $listing->productDetail->originCountry?->name ?? '—' }}</dd>
                            </div>
                            @if($listing->productDetail->warranty_period_months)
                                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                    <dt class="text-gray-400 font-medium text-[11px]">Warranty</dt>
                                    <dd class="font-semibold text-gray-800">{{ $listing->productDetail->warranty_period_months }} Months</dd>
                                </div>
                            @endif
                            @if($listing->productDetail->packaging_type)
                                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                    <dt class="text-gray-400 font-medium text-[11px]">Packaging</dt>
                                    <dd class="font-semibold text-gray-800">{{ $listing->productDetail->packaging_type }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                {{-- Service Detail Specifics --}}
                @if($listing->serviceDetail)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Service Scope & Deliverables</h4>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Service Type</dt>
                                <dd class="font-semibold text-gray-800">{{ ucfirst($listing->serviceDetail->service_type ?? 'Standard') }}</dd>
                            </div>
                            <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                                <dt class="text-gray-400 font-medium text-[11px]">Delivery Mode</dt>
                                <dd class="font-semibold text-gray-800">{{ ucfirst($listing->serviceDetail->delivery_mode ?? 'Remote / On-site') }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                {{-- Descriptions --}}
                @if($listing->short_description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs font-bold text-gray-700 mb-1">Short Summary</dt>
                        <dd class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100">{{ $listing->short_description }}</dd>
                    </div>
                @endif

                @if($listing->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs font-bold text-gray-700 mb-1">Detailed Description</dt>
                        <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-100 prose prose-xs max-w-none">
                            {!! nl2br(e($listing->description)) !!}
                        </div>
                    </div>
                @endif
            </x-backend.form-card>

            {{-- 3. Grouped Specifications --}}
            <x-backend.form-card title="Technical Specifications & Category Attributes">
                @if($groupedSpecifications->isEmpty())
                    <p class="text-xs text-gray-400 py-3 text-center">No specification attributes filled for this listing.</p>
                @else
                    <div class="space-y-4">
                        @foreach($groupedSpecifications as $group)
                            <div class="border border-gray-100 rounded-xl overflow-hidden">
                                <div class="bg-gray-50/80 px-3.5 py-2 border-b border-gray-100 flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-gray-800">{{ $group['group_name'] }}</h4>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ count($group['items']) }} specifications</span>
                                </div>
                                <div class="p-3.5 bg-white divide-y divide-gray-50">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                                        @foreach($group['items'] as $item)
                                            <div class="flex items-start justify-between gap-2 py-1 text-xs">
                                                <span class="text-gray-500 font-medium">{{ $item->attribute?->name ?? 'Attribute #' . $item->attribute_id }}:</span>
                                                <div class="text-right">
                                                    @if($item->attribute?->data_type === 'boolean')
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->value_boolean ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                            {{ $item->value_boolean ? 'Yes' : 'No' }}
                                                        </span>
                                                    @elseif($item->attribute?->data_type === 'multi_select' || is_array($item->value_json))
                                                        <div class="flex flex-wrap justify-end gap-1">
                                                            @foreach((array) ($item->value_json ?? explode(',', $item->value_text ?? '')) as $tag)
                                                                @if(trim($tag))
                                                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-medium border border-indigo-100">{{ trim($tag) }}</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @elseif($item->attributeValue)
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            @if($item->attributeValue->color_code)
                                                                <span class="w-3 h-3 rounded-full border border-gray-300" style="background-color: {{ $item->attributeValue->color_code }}"></span>
                                                            @endif
                                                            <span class="font-bold text-gray-900">{{ $item->attributeValue->value }}</span>
                                                        </div>
                                                    @elseif($item->value_number !== null)
                                                        <span class="font-bold text-gray-900">{{ $item->value_number }} {{ $item->attribute?->unit?->symbol }}</span>
                                                    @else
                                                        <span class="font-bold text-gray-900">{{ $item->value_text ?? '—' }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>

            {{-- 4. Product Variants Matrix (if variable) --}}
            @if($listing->variants->isNotEmpty())
                <x-backend.form-card title="Product Variants ({{ $listing->variants->count() }})">
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-y border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Variant Name &amp; Specifications</th>
                                    <th class="px-4 py-3 font-semibold">SKU</th>
                                    <th class="px-4 py-3 font-semibold">Price</th>
                                    <th class="px-4 py-3 font-semibold">Stock</th>
                                    <th class="px-4 py-3 font-semibold text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($listing->variants as $variant)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-5 py-3">
                                            <p class="font-bold text-gray-900 mb-1">{{ $variant->name }}</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($variant->variantAttributes as $varAttr)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700 border border-gray-200">
                                                        <span class="text-gray-400 font-normal">{{ $varAttr->attribute?->name }}:</span>
                                                        <strong class="font-semibold">{{ $varAttr->resolvedValue() }}</strong>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-mono font-medium text-gray-600">{{ $variant->sku ?? '—' }}</td>
                                        <td class="px-4 py-3 font-bold text-indigo-700">
                                            {{ $listing->currency_code }} {{ number_format($variant->price, 2) }}
                                            @if($variant->compare_at_price)
                                                <span class="block text-[10px] text-gray-400 line-through">{{ $listing->currency_code }} {{ number_format($variant->compare_at_price, 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-800">{{ $variant->stock_quantity }}</span>
                                            <span class="block text-[10px] text-gray-400">{{ str_replace('_', ' ', $variant->stock_status) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span @class([
                                                'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                                'bg-emerald-100 text-emerald-700' => $variant->is_active,
                                                'bg-gray-100 text-gray-500' => !$variant->is_active,
                                            ])>
                                                {{ $variant->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-backend.form-card>
            @endif

            {{-- 5. Volume Discount / Tier Pricing --}}
            @if($listing->allTierPrices->isNotEmpty())
                <x-backend.form-card title="Quantity Break / Tier Pricing (Volume Discounts)">
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-y border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-5 py-2.5 font-semibold">Scope / Variant</th>
                                    <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($listing->allTierPrices as $tp)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-5 py-2.5 font-medium text-gray-700">
                                            @if($tp->listing_variant_id && $tp->listingVariant)
                                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold text-[11px] border border-indigo-100">
                                                    Variant: {{ $tp->listingVariant->name }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-medium text-[11px]">
                                                    Global (All Variants)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 font-medium text-gray-800">
                                            {{ (int)$tp->min_quantity }} &ndash; {{ $tp->max_quantity ? (int)$tp->max_quantity : '∞' }} units
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-bold text-indigo-700">
                                            {{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-backend.form-card>
            @endif

        </div>

        {{-- Right 4 Columns: Supplier Profile, Audit & Moderation Metadata --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Supplier Card --}}
            <x-backend.form-card title="Supplier Organization">
                @if($listing->supplierAccount)
                    @php($profile = $listing->supplierAccount->supplierProfile)
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shadow-xs">
                                {{ strtoupper(substr($profile?->display_name ?? $listing->supplierAccount->display_name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $profile?->display_name ?? $listing->supplierAccount->display_name }}</p>
                                <p class="text-[11px] text-gray-500">{{ $profile?->country?->name ?? 'International Supplier' }}</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 space-y-1.5 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Account ID:</span>
                                <span class="font-mono font-medium text-gray-800">#{{ $listing->supplierAccount->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status:</span>
                                <span class="font-semibold text-emerald-600">{{ ucfirst($listing->supplierAccount->status ?? 'Active') }}</span>
                            </div>
                        </div>

                        <a href="{{ route('admin.suppliers.show', $listing->supplierAccount) }}" class="btn-primary w-full text-center text-xs font-semibold py-2 rounded-lg block">
                            View Supplier Account Profile &rarr;
                        </a>
                    </div>
                @else
                    <p class="text-xs text-gray-400">No supplier account linked.</p>
                @endif
            </x-backend.form-card>

            {{-- Moderation Timeline --}}
            <x-backend.form-card title="Moderation & Audit Timeline">
                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Created Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Last Updated</span>
                        <span class="font-medium text-gray-800">{{ $listing->updated_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Published Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->published_at ? $listing->published_at->format('d M Y') : 'Not Published' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Approved By</span>
                        <span class="font-medium text-gray-800">{{ $listing->approvedBy?->name ?? '—' }}</span>
                    </div>
                    @if($listing->approved_at)
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-400">Approved At</span>
                            <span class="font-medium text-gray-800">{{ $listing->approved_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif
                </dl>

                @if($listing->rejection_reason)
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs">
                        <p class="font-bold text-rose-800 mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Rejection / Suspension Reason:</p>
                        <p class="text-rose-700 leading-relaxed">{{ $listing->rejection_reason }}</p>
                    </div>
                @endif
            </x-backend.form-card>

        </div>

    </div>

    {{-- Reject Modal --}}
    @if($listing->approval_status === 'pending')
        <x-backend.modal id="reject" title="Reject Listing">
            <form method="POST" action="{{ route('admin.catalog.listings.reject', $listing) }}">
                @csrf
                <div class="space-y-3">
                    <p class="text-xs text-gray-500">
                        Please provide a clear reason for rejecting this listing. The supplier will see this reason and can make necessary revisions.
                    </p>
                    <x-backend.textarea name="reason" label="Rejection Note / Feedback to Supplier" placeholder="e.g. Incomplete specifications, invalid brand claim, low resolution images..." required />
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Confirm Rejection</button>
                </div>
            </form>
        </x-backend.modal>
    @elseif($listing->is_active)
        <x-backend.modal id="deactivate" title="Suspend / Deactivate Listing">
            <form method="POST" action="{{ route('admin.catalog.listings.deactivate', $listing) }}">
                @csrf
                <div class="space-y-3">
                    <p class="text-xs text-gray-500">
                        Enter the reason for taking down this active listing from the marketplace.
                    </p>
                    <x-backend.textarea name="reason" label="Suspension Reason" placeholder="e.g. Policy violation, out of stock dispute..." required />
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Suspend Listing</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
