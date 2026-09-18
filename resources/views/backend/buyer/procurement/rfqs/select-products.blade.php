@extends('backend.layouts.buyer')

@section('title', 'Select Products for RFQ')
@section('breadcrumb', 'Procurement / RFQs / Select Products')

@push('styles')
<style>
    /* Mirrors the public marketplace's (/v2/categories) tab + card look so
       this page doesn't feel like a different product mid-flow. */
    .category-tab-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1rem;
        border-radius: 9999px;
        border-width: 1px;
        border-style: solid;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .category-tab-btn.active {
        background-color: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    .category-tab-btn:not(.active) {
        background-color: #ffffff;
        color: #4b5563;
        border-color: #e5e7eb;
    }
    .category-tab-btn:not(.active):hover {
        border-color: #10b981;
        color: #059669;
        background-color: #f0fdf4;
    }
    .badge-verified {
        background: #e8f5ee;
        color: #1a7f45;
        border: 1px solid #b6e0c6;
    }
    @keyframes productCardFadeUp {
        0% { opacity: 0; transform: translateY(18px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .product-card-fade-up {
        animation: productCardFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        will-change: transform, opacity;
    }
</style>
@endpush

@section('body')

    <div
        x-data="productSelector({ returnUrl: {{ json_encode($returnUrl) }} })"
        x-init="init()"
        class="pb-24"
    >
        <x-backend.page-header title="Select Marketplace Products" subtitle="Search and select one or more products or services to add to your RFQ.">
            <x-slot:actions>
                <a href="{{ $returnUrl }}" @click.prevent="cancel()"
                   class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to RFQ
                </a>
            </x-slot:actions>
        </x-backend.page-header>

        {{-- Search --}}
        <form method="GET" action="{{ route('buyer.select-products-for-rfq') }}" class="mb-5">
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="category" value="{{ $category ?: '' }}">
            <div class="relative max-w-xl">
                <div class="flex items-center bg-white rounded-xl shadow-xs overflow-hidden border border-gray-200 focus-within:border-emerald-500 transition-colors">
                    <span class="pl-4 text-gray-400 shrink-0">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search products, brands..."
                           class="flex-1 px-4 py-3 text-sm text-gray-800 placeholder-gray-400 bg-transparent focus:outline-none">
                    @if($search)
                        <a href="{{ route('buyer.select-products-for-rfq', array_filter(['return_url' => $returnUrl, 'type' => $type, 'category' => $category])) }}"
                           class="pr-4 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Type toggle --}}
        <div class="flex items-center gap-2 flex-wrap mb-3">
            @foreach(['' => 'All Types', 'product' => 'Products', 'service' => 'Services'] as $value => $label)
                <a href="{{ route('buyer.select-products-for-rfq', array_filter(['return_url' => $returnUrl, 'search' => $search, 'category' => $category, 'type' => $value])) }}"
                   class="category-tab-btn {{ $type === $value ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Category tabs --}}
        <div class="flex items-center gap-2 flex-wrap mb-6">
            <a href="{{ route('buyer.select-products-for-rfq', array_filter(['return_url' => $returnUrl, 'search' => $search, 'type' => $type])) }}"
               class="category-tab-btn {{ !$category ? 'active' : '' }}">
                All Categories
            </a>
            @foreach($categories as $c)
                <a href="{{ route('buyer.select-products-for-rfq', array_filter(['return_url' => $returnUrl, 'search' => $search, 'type' => $type, 'category' => $c->id])) }}"
                   class="category-tab-btn {{ $category === $c->id ? 'active' : '' }}">
                    {{ $c->name }}
                </a>
            @endforeach
        </div>

        {{-- Results count --}}
        <p class="text-sm text-gray-500 mb-5">
            <span class="font-semibold text-gray-900">{{ number_format($listings->total()) }}</span>
            {{ Str::plural('product', $listings->total()) }} found
            @if($search) for "<span class="text-emerald-600 font-medium">{{ $search }}</span>"@endif
            @if($category) in <span class="text-emerald-600 font-medium">{{ $categories->firstWhere('id', $category)?->name }}</span>@endif
        </p>

        {{-- Selected count banner --}}
        <div x-show="selectedCount > 0" x-cloak class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm text-emerald-800 font-medium">
                <span x-text="selectedCount"></span> product<span x-show="selectedCount !== 1">s</span> selected
            </p>
            <button type="button" @click="clearAll()" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">
                <i class="fa-solid fa-xmark mr-1"></i> Clear selection
            </button>
        </div>

        {{-- Product grid --}}
        @if($listings->isEmpty())
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                </div>
                <p class="text-base font-semibold text-gray-800">No products found</p>
                <p class="text-sm text-gray-400 mt-1">Try a different category or search term</p>
                <a href="{{ route('buyer.select-products-for-rfq', ['return_url' => $returnUrl]) }}"
                   class="mt-4 inline-flex items-center gap-1 text-sm text-emerald-600 font-medium hover:text-emerald-700 hover:underline">
                    Clear filters &rarr;
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-6">
                @foreach($listings as $listing)
                    @php
                        $img = $listing->primaryImage?->getUrl()
                            ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                            ?? $listing->getFirstMediaUrl('gallery')
                            ?: null;
                        $productData = [
                            'id' => $listing->id,
                            'name' => $listing->name,
                            'image' => $img,
                            'category' => $listing->mainCategory?->name,
                            'supplier' => $listing->supplierAccount?->supplierProfile?->display_name,
                            'price' => $listing->base_price,
                            'currency' => $listing->currency_code,
                            'type' => $listing->listing_type,
                        ];
                    @endphp
                    <div @click="toggle({{ Illuminate\Support\Js::from($productData) }})"
                         class="group bg-white border rounded-xl overflow-hidden transition-all duration-200 product-card-fade-up relative flex flex-col h-full cursor-pointer"
                         style="animation-delay: {{ $loop->index * 55 }}ms"
                         :class="isSelected({{ $listing->id }}) ? 'border-emerald-500 ring-2 ring-emerald-500/30 shadow-md' : 'border-gray-200 hover:border-emerald-200 hover:shadow-md'">

                        {{-- Image --}}
                        <div class="relative h-48 bg-gray-100 overflow-hidden shrink-0">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $listing->name }}" loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                    <i class="fa-solid fa-box text-4xl mb-1"></i>
                                    <span class="text-xs">No image</span>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 z-10 pointer-events-none">
                                @if($listing->supplierAccount?->supplierProfile)
                                    <span class="badge-verified text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i> Verified
                                    </span>
                                @endif
                                @if($listing->is_featured)
                                    <span class="bg-amber-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1 uppercase tracking-wide">
                                        <i class="fa-solid fa-star text-[9px]"></i> Featured
                                    </span>
                                @endif
                            </div>

                            {{-- Selection checkbox --}}
                            <div class="absolute top-2.5 right-2.5 z-10 w-7 h-7 rounded-full flex items-center justify-center shadow-xs border transition-colors"
                                 :class="isSelected({{ $listing->id }}) ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white/95 border-gray-200 text-transparent'">
                                <i class="fa-solid fa-check text-[11px]"></i>
                            </div>

                            {{-- View details --}}
                            <a href="{{ route('buyer.marketplace.products.show', $listing) }}" target="_blank" @click.stop
                               class="absolute bottom-2.5 right-2.5 z-10 w-8 h-8 rounded-full flex items-center justify-center bg-white/95 text-slate-500 border border-slate-200/90 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all shadow-xs"
                               title="View details">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>

                        {{-- Body --}}
                        <div class="p-3.5 flex flex-col flex-1">
                            <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mb-1 truncate">{{ $listing->mainCategory?->name ?? 'Other' }}</p>
                            <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-0.5 line-clamp-2 group-hover:text-emerald-700 transition-colors">{{ $listing->name }}</h3>
                            <p class="text-xs text-gray-500 mb-3 truncate">{{ $listing->supplierAccount?->supplierProfile?->display_name ?? '—' }}</p>

                            <div class="mt-auto pt-2 flex items-center justify-between border-t border-gray-100">
                                <div>
                                    @if($listing->base_price)
                                        <p class="text-sm font-bold text-emerald-600">
                                            {{ $listing->currency_code }} {{ number_format($listing->base_price, 0) }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 italic">Price on request</p>
                                    @endif
                                </div>
                                <span class="text-xs font-semibold flex items-center gap-1"
                                      :class="isSelected({{ $listing->id }}) ? 'text-emerald-600' : 'text-gray-400 group-hover:text-emerald-600'">
                                    <span x-text="isSelected({{ $listing->id }}) ? 'Selected' : 'Select'"></span>
                                    <i class="fa-solid" :class="isSelected({{ $listing->id }}) ? 'fa-check' : 'fa-plus'"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-backend.pagination :paginator="$listings" />
        @endif

        {{-- Sticky bottom action bar --}}
        <div x-show="selectedCount > 0" x-cloak
             class="fixed bottom-0 left-0 right-0 lg:left-64 bg-white border-t border-gray-200 shadow-lg px-6 py-4 z-30">
            <div class="flex items-center justify-between max-w-6xl mx-auto">
                <p class="text-sm text-gray-700">
                    <span class="font-bold text-gray-900" x-text="selectedCount"></span> product<span x-show="selectedCount !== 1">s</span> selected
                </p>
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="cancel()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Cancel &amp; Back to RFQ</span>
                    </button>
                    <button type="button" @click="continueToRfq()"
                            class="text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors">
                        <i class="fa-solid fa-cart-plus"></i> Add Selected Products to RFQ
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productSelector', (config) => ({
            storageKey: 'rfqSelectorSelectedProducts',
            selected: {},

            init() {
                try {
                    const raw = sessionStorage.getItem(this.storageKey);
                    if (raw) this.selected = JSON.parse(raw);
                } catch (e) {
                    this.selected = {};
                }
            },
            persist() {
                try { sessionStorage.setItem(this.storageKey, JSON.stringify(this.selected)); } catch (e) {}
            },
            isSelected(id) {
                return !!this.selected[id];
            },
            toggle(product) {
                if (this.selected[product.id]) {
                    delete this.selected[product.id];
                } else {
                    this.selected[product.id] = product;
                }
                this.persist();
            },
            clearAll() {
                this.selected = {};
                this.persist();
            },
            get selectedCount() {
                return Object.keys(this.selected).length;
            },
            buildReturnUrl(withSelection) {
                const url = new URL(config.returnUrl, window.location.origin);
                url.searchParams.set('restore_items', '1');
                if (withSelection) {
                    url.searchParams.set('selected_listings', Object.keys(this.selected).join(','));
                }
                return url.toString();
            },
            cancel() {
                window.location.href = this.buildReturnUrl(false);
            },
            continueToRfq() {
                if (this.selectedCount === 0) return;
                const url = this.buildReturnUrl(true);
                try { sessionStorage.removeItem(this.storageKey); } catch (e) {}
                window.location.href = url;
            },
        }));
    });
</script>
@endpush
