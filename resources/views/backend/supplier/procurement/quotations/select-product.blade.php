@extends('backend.layouts.supplier')

@section('title', 'Select Products to Offer — ' . $rfqItem->item_name)
@section('breadcrumb', 'Quotations / Select Products')

@push('styles')
<style>
    .category-tab-btn {
        display: inline-flex; align-items: center; padding: 0.375rem 1rem;
        font-size: 0.75rem; font-weight: 600; line-height: 1rem; border-radius: 9999px;
        border-width: 1px; border-style: solid; cursor: pointer; user-select: none;
        white-space: nowrap; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .category-tab-btn.active { background-color: #0f172a; color: #fff; border-color: #0f172a; }
    .category-tab-btn:not(.active) { background-color: #fff; color: #4b5563; border-color: #e5e7eb; }
    .category-tab-btn:not(.active):hover { border-color: var(--theme-primary, #4f46e5); color: var(--theme-primary, #4f46e5); }
</style>
@endpush

@section('body')

    <div x-data="quotationProductSelector({
        returnUrl: {{ json_encode($returnUrl) }},
        itemToken: {{ json_encode($itemToken) }},
        rfqItemId: {{ (int) $rfqItem->id }},
        activeTab: '{{ $activeTab ?? 'matching' }}',
        allowAlternativeProducts: {{ $rfq->allow_alternative_products ? 'true' : 'false' }}
    })" class="pb-28">

        {{-- Page Header --}}
        <x-backend.page-header title="Select Products to Offer" subtitle="Choose one or multiple products from your catalog to quote against the buyer's requirement.">
            <x-slot:actions>
                <a href="{{ $returnUrl }}" @click.prevent="cancel()"
                   class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Quotation
                </a>
            </x-slot:actions>
        </x-backend.page-header>

        {{-- Buyer Requirement Card --}}
        <div class="bg-gradient-to-r from-slate-50 to-indigo-50/40 border border-slate-200 rounded-xl p-4 sm:p-5 mb-6 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 border border-indigo-200">
                            <i class="fa-solid fa-bullseye text-indigo-600 text-[10px]"></i> Buyer Requirement
                        </span>
                        <span class="text-xs font-semibold text-gray-500">
                            RFQ: {{ $rfq->rfq_number }}
                        </span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 leading-snug">{{ $rfqItem->item_name }}</h2>
                    @if($rfqItem->description)
                        <p class="text-xs text-gray-600 line-clamp-2 max-w-3xl">{{ $rfqItem->description }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 text-center min-w-[90px] shadow-2xs">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Quantity</span>
                        <span class="text-sm font-bold text-slate-900">
                            {{ rtrim(rtrim((string) $rfqItem->quantity, '0'), '.') }}
                            <span class="text-xs text-slate-500 font-semibold">{{ $rfqItem->unit?->symbol ?: ($rfqItem->unit?->name ?: 'units') }}</span>
                        </span>
                    </div>
                    @if(isset($relevantCategories) && $relevantCategories->isNotEmpty())
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 text-left shadow-2xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $relevantCategories->count() > 1 ? 'Relevant Categories' : 'Category' }}
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                @foreach($relevantCategories as $cat)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded {{ $cat->id === $rfqItem->category_id ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $cat->name }}
                                        @if($cat->id === $rfqItem->category_id)
                                            <span class="text-[9px] uppercase tracking-wider text-indigo-400 font-bold">(Primary)</span>
                                        @else
                                            <span class="text-[9px] uppercase tracking-wider text-amber-500 font-bold">(Suggested)</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @elseif($rfqItem->category)
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 text-center shadow-2xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Category</span>
                            <span class="text-xs font-bold text-indigo-700">{{ $rfqItem->category->name }}</span>
                        </div>
                    @endif
                    @if($rfqItem->estimated_unit_price)
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 text-center shadow-2xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Unit Price</span>
                            <span class="text-xs font-bold text-emerald-700">{{ $rfq->currency_code }} {{ number_format($rfqItem->estimated_unit_price, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Navigation Tabs: Matching Products (Primary) vs Browse All Products --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-2 -mb-px">
                <button type="button" @click="tab = 'matching'"
                        class="flex items-center gap-2 py-3 px-4 border-b-2 text-sm font-semibold transition-colors focus:outline-none"
                        :class="tab === 'matching' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>Matching Products</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                          :class="tab === 'matching' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600'">
                        {{ $matches->count() }}
                    </span>
                </button>

                <button type="button" @click="tab = 'browse'"
                        class="flex items-center gap-2 py-3 px-4 border-b-2 text-sm font-semibold transition-colors focus:outline-none"
                        :class="tab === 'browse' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-store text-xs"></i>
                    <span>Browse All Products</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                          :class="tab === 'browse' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600'">
                        {{ $listings->total() }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             TAB 1: MATCHING PRODUCTS (PRIMARY / DEFAULT)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'matching'" x-cloak>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i> Suggested Matches from Your Catalog
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ranked by similarity in category, keywords, and specifications against this buyer's requirement.</p>
                </div>
                <div class="text-xs text-gray-500">
                    <span class="font-bold text-gray-900">{{ $matches->count() }}</span> suggestions available
                </div>
            </div>

            @if($matches->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 bg-white border border-dashed border-gray-200 rounded-xl text-center">
                    <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center mb-3 text-indigo-500">
                        <i class="fa-solid fa-lightbulb text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900">No close matches found</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-md">We couldn't automatically match this requirement to your existing listings with high confidence.</p>
                    <button type="button" @click="tab = 'browse'" class="mt-4 btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass"></i> Browse all your catalog products
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($matches as $listing)
                        @php
                            $img = $listing->primaryImage?->getUrl()
                                ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                                ?? $listing->getFirstMediaUrl('gallery') ?: null;
                            $productUrl = $listing->slug ? route('v2.products.show', $listing->slug) : url('/v2/product/' . $listing->id);
                            $productData = [
                                'id' => $listing->id,
                                'name' => $listing->name,
                                'image' => $img,
                                'category' => $listing->mainCategory?->name,
                                'price' => $listing->base_price,
                                'currency' => $listing->currency_code,
                                'slug' => $listing->slug,
                            ];
                            $matchPct = $listing->match_percentage ?? round($listing->match_score * 100);
                        @endphp
                        <div @click="toggleSelect({{ Illuminate\Support\Js::from($productData) }})"
                             class="group bg-white border rounded-xl overflow-hidden cursor-pointer transition-all duration-200 flex flex-col h-full relative"
                             :class="isSelected({{ $listing->id }}) ? 'border-indigo-600 ring-2 ring-indigo-500/30 shadow-md bg-indigo-50/10' : 'border-gray-200 hover:border-indigo-200 hover:shadow-sm'">

                            {{-- Product Image & Match Badge --}}
                            <div class="relative h-40 bg-gray-100 overflow-hidden shrink-0">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $listing->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                        <i class="fa-solid fa-box text-3xl mb-1"></i>
                                        <span class="text-[11px]">No image</span>
                                    </div>
                                @endif

                                {{-- Match Score Badge --}}
                                <div class="absolute top-2 left-2 z-10">
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1 border
                                        {{ $matchPct >= 80 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($matchPct >= 60 ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-amber-100 text-amber-800 border-amber-300') }}">
                                        <i class="fa-solid fa-sparkles text-[9px]"></i>
                                        <span>{{ $matchPct }}% match</span>
                                    </span>
                                </div>

                                {{-- Selection Checkbox --}}
                                <div class="absolute top-2 right-2 z-10 w-7 h-7 rounded-full flex items-center justify-center shadow-xs border transition-colors"
                                     :class="isSelected({{ $listing->id }}) ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white/95 border-gray-300 text-transparent group-hover:border-indigo-400'">
                                    <i class="fa-solid fa-check text-[11px]"></i>
                                </div>

                                {{-- View Product Details Badge in New Tab --}}
                                <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" @click.stop
                                   class="absolute bottom-2 right-2 z-10 px-2 py-0.5 rounded-md bg-black/60 hover:bg-indigo-600 text-white text-[10px] font-semibold backdrop-blur-xs flex items-center gap-1 transition-all shadow-xs"
                                   title="Open product details in new tab">
                                    <span>Details</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-gray-200"></i>
                                </a>
                            </div>

                            {{-- Card Body --}}
                            <div class="p-3.5 flex flex-col flex-1">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-1">
                                    <i class="fa-solid fa-folder text-[9px]"></i>
                                    <span class="truncate">{{ $listing->mainCategory?->name ?? 'General' }}</span>
                                </div>

                                <div class="flex items-start justify-between gap-1.5 mb-2">
                                    <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2" title="{{ $listing->name }}">
                                        {{ $listing->name }}
                                    </h4>
                                    <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" @click.stop
                                       class="text-gray-400 hover:text-indigo-600 p-0.5 shrink-0 transition-colors"
                                       title="Open product details in new tab">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>

                                {{-- Matched Reasons --}}
                                @if(!empty($listing->matched_reasons))
                                    <div class="bg-slate-50 border border-slate-100 rounded-lg p-2 mb-3 space-y-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Matched because:</p>
                                        @foreach(array_slice($listing->matched_reasons, 0, 3) as $reason)
                                            <p class="text-[11px] text-emerald-700 font-medium flex items-center gap-1 leading-tight">
                                                <i class="fa-solid fa-check text-[9px] text-emerald-600 shrink-0"></i>
                                                <span class="truncate">{{ $reason }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Footer: Price & Select Action --}}
                                <div class="mt-auto pt-2.5 flex items-center justify-between border-t border-gray-100">
                                    <div>
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Catalog Price</span>
                                        @if($listing->base_price)
                                            <span class="text-sm font-bold text-indigo-600">{{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}</span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Custom Quote</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ $productUrl }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           @click.stop
                                           class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex items-center gap-1 shadow-2xs"
                                           title="Open product details page in new tab">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            <span>Details</span>
                                        </a>

                                        <button type="button" @click.stop="toggleSelect({{ Illuminate\Support\Js::from($productData) }})"
                                                class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors flex items-center gap-1.5"
                                                :class="isSelected({{ $listing->id }}) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 text-gray-700 border-gray-200 group-hover:bg-indigo-50 group-hover:text-indigo-700 group-hover:border-indigo-200'">
                                            <i class="fa-solid" :class="isSelected({{ $listing->id }}) ? 'fa-check' : 'fa-plus'"></i>
                                            <span x-text="isSelected({{ $listing->id }}) ? 'Selected' : 'Select'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             TAB 2: BROWSE ALL PRODUCTS
             ══════════════════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'browse'" x-cloak>
            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('supplier.select-products-for-quotation') }}" class="mb-5 bg-white border border-gray-200 rounded-xl p-3.5 shadow-2xs space-y-3">
                <input type="hidden" name="rfq_item_id" value="{{ $rfqItem->id }}">
                <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                <input type="hidden" name="item_token" value="{{ $itemToken }}">
                <input type="hidden" name="category" value="{{ $category ?: '' }}">
                <input type="hidden" name="tab" value="browse">

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Search Input --}}
                    <div class="relative flex-1 min-w-[220px]">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search products by title, SKU, or description..."
                               class="focus-accent w-full pl-9 pr-3 py-2 text-xs border border-gray-300 rounded-lg text-gray-800 placeholder-gray-400 bg-white">
                    </div>

                    {{-- Brand Filter --}}
                    @if($brands->isNotEmpty())
                        <div class="w-44">
                            <select name="brand" class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-700">
                                <option value="">All Brands</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" @selected($brandId === $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Price Filters --}}
                    <div class="flex items-center gap-1.5">
                        <input type="number" step="0.01" name="price_min" value="{{ $priceMin }}" placeholder="Min price" class="focus-accent w-24 px-2.5 py-2 border border-gray-300 rounded-lg text-xs">
                        <span class="text-gray-400 text-xs">—</span>
                        <input type="number" step="0.01" name="price_max" value="{{ $priceMax }}" placeholder="Max price" class="focus-accent w-24 px-2.5 py-2 border border-gray-300 rounded-lg text-xs">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-filter text-[10px]"></i> Filter
                        </button>
                        @if($search || $category || $brandId || $priceMin || $priceMax)
                            <a href="{{ route('supplier.select-products-for-quotation', ['rfq_item_id' => $rfqItem->id, 'return_url' => $returnUrl, 'item_token' => $itemToken, 'tab' => 'browse']) }}"
                               class="text-xs text-gray-500 hover:text-gray-700 px-2.5 py-2 font-medium">Reset</a>
                        @endif
                    </div>
                </div>

                {{-- Category Pill Tabs --}}
                @if($categories->isNotEmpty())
                    <div class="pt-2 border-t border-gray-100 flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mr-1">Categories:</span>
                        <a href="{{ route('supplier.select-products-for-quotation', array_filter(['rfq_item_id' => $rfqItem->id, 'return_url' => $returnUrl, 'item_token' => $itemToken, 'search' => $search, 'brand' => $brandId, 'tab' => 'browse'])) }}"
                           class="category-tab-btn {{ !$category ? 'active' : '' }}">All ({{ $listings->total() }})</a>
                        @foreach($categories as $c)
                            <a href="{{ route('supplier.select-products-for-quotation', array_filter(['rfq_item_id' => $rfqItem->id, 'return_url' => $returnUrl, 'item_token' => $itemToken, 'search' => $search, 'brand' => $brandId, 'category' => $c->id, 'tab' => 'browse'])) }}"
                               class="category-tab-btn {{ $category === $c->id ? 'active' : '' }}">{{ $c->name }} ({{ $c->listing_count }})</a>
                        @endforeach
                    </div>
                @endif
            </form>

            {{-- Products Grid --}}
            @if($listings->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 bg-white border border-gray-200 rounded-xl text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-800">No products found</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm">Try adjusting your search keywords, category filters, or price range.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                    @foreach($listings as $listing)
                        @php
                            $img = $listing->primaryImage?->getUrl()
                                ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                                ?? $listing->getFirstMediaUrl('gallery') ?: null;
                            $productUrl = $listing->slug ? route('v2.products.show', $listing->slug) : url('/v2/product/' . $listing->id);
                            $productData = [
                                'id' => $listing->id,
                                'name' => $listing->name,
                                'image' => $img,
                                'category' => $listing->mainCategory?->name,
                                'price' => $listing->base_price,
                                'currency' => $listing->currency_code,
                                'slug' => $listing->slug,
                            ];
                        @endphp
                        <div @click="toggleSelect({{ Illuminate\Support\Js::from($productData) }})"
                             class="group bg-white border rounded-xl overflow-hidden cursor-pointer transition-all duration-200 flex flex-col h-full relative"
                             :class="isSelected({{ $listing->id }}) ? 'border-indigo-600 ring-2 ring-indigo-500/30 shadow-md bg-indigo-50/10' : 'border-gray-200 hover:border-indigo-200 hover:shadow-sm'">

                            <div class="relative h-40 bg-gray-100 overflow-hidden shrink-0">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $listing->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                        <i class="fa-solid fa-box text-3xl mb-1"></i>
                                        <span class="text-[11px]">No image</span>
                                    </div>
                                @endif

                                {{-- Selection Checkbox --}}
                                <div class="absolute top-2 right-2 z-10 w-7 h-7 rounded-full flex items-center justify-center shadow-xs border transition-colors"
                                     :class="isSelected({{ $listing->id }}) ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white/95 border-gray-300 text-transparent group-hover:border-indigo-400'">
                                    <i class="fa-solid fa-check text-[11px]"></i>
                                </div>

                                {{-- View Product Details Badge in New Tab --}}
                                <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" @click.stop
                                   class="absolute bottom-2 right-2 z-10 px-2 py-0.5 rounded-md bg-black/60 hover:bg-indigo-600 text-white text-[10px] font-semibold backdrop-blur-xs flex items-center gap-1 transition-all shadow-xs"
                                   title="Open product details in new tab">
                                    <span>Details</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-gray-200"></i>
                                </a>
                            </div>

                            <div class="p-3.5 flex flex-col flex-1">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-1">
                                    <i class="fa-solid fa-folder text-[9px]"></i>
                                    <span class="truncate">{{ $listing->mainCategory?->name ?? 'General' }}</span>
                                </div>

                                <div class="flex items-start justify-between gap-1.5 mb-2">
                                    <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2" title="{{ $listing->name }}">
                                        {{ $listing->name }}
                                    </h4>
                                    <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" @click.stop
                                       class="text-gray-400 hover:text-indigo-600 p-0.5 shrink-0 transition-colors"
                                       title="Open product details in new tab">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>

                                @if($listing->brand)
                                    <p class="text-[11px] text-gray-500 mb-2">
                                        Brand: <span class="font-medium text-gray-700">{{ $listing->brand->name }}</span>
                                    </p>
                                @endif

                                <div class="mt-auto pt-2.5 flex items-center justify-between border-t border-gray-100">
                                    <div>
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Base Price</span>
                                        @if($listing->base_price)
                                            <span class="text-sm font-bold text-indigo-600">{{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}</span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Custom Quote</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ $productUrl }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           @click.stop
                                           class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex items-center gap-1 shadow-2xs"
                                           title="Open product details page in new tab">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            <span>Details</span>
                                        </a>

                                        <button type="button" @click.stop="toggleSelect({{ Illuminate\Support\Js::from($productData) }})"
                                                class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors flex items-center gap-1.5"
                                                :class="isSelected({{ $listing->id }}) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 text-gray-700 border-gray-200 group-hover:bg-indigo-50 group-hover:text-indigo-700 group-hover:border-indigo-200'">
                                            <i class="fa-solid" :class="isSelected({{ $listing->id }}) ? 'fa-check' : 'fa-plus'"></i>
                                            <span x-text="isSelected({{ $listing->id }}) ? 'Selected' : 'Select'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-backend.pagination :paginator="$listings" />
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             STICKY BOTTOM ACTION BAR (Multi-Offer Confirmation)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div x-show="selectedListings.length > 0" x-cloak
             class="fixed bottom-0 left-0 right-0 lg:left-64 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-2xl px-4 sm:px-6 py-3.5 z-40 transition-all">
            <div class="flex flex-col sm:flex-row items-center justify-between max-w-6xl mx-auto gap-3">
                <div class="flex items-center gap-2.5 overflow-x-auto max-w-full w-full sm:w-auto pb-1 sm:pb-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 shrink-0">
                        Selected (<span x-text="selectedListings.length"></span>):
                    </span>
                    <div class="flex items-center gap-1.5 flex-nowrap shrink-0">
                        <template x-for="(prod, pIdx) in selectedListings" :key="prod.id">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200 shrink-0">
                                <span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[9px]" x-text="pIdx + 1"></span>
                                <span class="max-w-[140px] truncate" x-text="prod.name"></span>
                                <button type="button" @click.stop="removeSelected(prod.id)" class="text-indigo-400 hover:text-indigo-700 ml-0.5">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </span>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0 ml-auto">
                    <button type="button" @click="cancel()"
                            class="text-xs font-semibold px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" @click="confirmCreateOffers()"
                            class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 shadow-sm transition-all hover:scale-[1.02]">
                        <i class="fa-solid fa-check"></i>
                        <span>Create <span x-text="selectedListings.length > 1 ? selectedListings.length + ' Offers' : 'Offer'"></span></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quotationProductSelector', (config) => ({
            tab: config.activeTab || 'matching',
            selectedListings: [],

            isSelected(id) {
                return this.selectedListings.some(item => item.id === id);
            },
            toggleSelect(product) {
                const idx = this.selectedListings.findIndex(item => item.id === product.id);
                if (idx !== -1) {
                    this.selectedListings.splice(idx, 1);
                } else {
                    if (!config.allowAlternativeProducts) {
                        this.selectedListings = [product];
                    } else {
                        this.selectedListings.push(product);
                    }
                }
            },
            removeSelected(id) {
                this.selectedListings = this.selectedListings.filter(item => item.id !== id);
            },
            buildReturnUrl(withSelection) {
                const url = new URL(config.returnUrl, window.location.origin);
                url.searchParams.set('restore_items', '1');
                if (withSelection && this.selectedListings.length > 0) {
                    url.searchParams.set('selected_listing_ids', this.selectedListings.map(p => p.id).join(','));
                    url.searchParams.set('item_token', config.itemToken);
                    url.searchParams.set('rfq_item_id', config.rfqItemId);
                }
                return url.toString();
            },
            cancel() {
                window.location.href = this.buildReturnUrl(false);
            },
            confirmCreateOffers() {
                if (this.selectedListings.length === 0) return;
                window.location.href = this.buildReturnUrl(true);
            },
        }));
    });
</script>
@endpush
