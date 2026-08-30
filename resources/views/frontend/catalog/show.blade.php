@extends('frontend.layouts.master')

@section('title', $listing->name.' — EduShopify')
@section('meta_description', Str::limit(strip_tags($listing->short_description ?? $listing->description ?? $listing->name), 155))

@push('styles')
<style>
    /* ── Gallery ───────────────────────────────────────────────── */
    .pdp-thumb { transition: border-color .2s, opacity .2s; }
    .pdp-thumb.active { border-color: var(--fe-primary) !important; opacity: 1; }
    .pdp-thumb:not(.active) { opacity: .55; }
    .pdp-thumb:not(.active):hover { opacity: 1; }

    /* ── Lightbox ──────────────────────────────────────────────── */
    #pdp-lightbox { display:none !important; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,.88); backdrop-filter:blur(6px);
        align-items:center; justify-content:center; }
    #pdp-lightbox.open { display:flex !important; }
    #pdp-lightbox img { max-height:88vh; max-width:90vw; object-fit:contain;
        border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.5); }
    .lb-arrow { position:absolute; top:50%; transform:translateY(-50%);
        background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
        backdrop-filter:blur(8px); color:#fff; width:48px; height:48px;
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        cursor:pointer; transition:background .2s; font-size:18px; }
    .lb-arrow:hover { background:rgba(255,255,255,.25); }
    .lb-arrow.prev { left:20px; }
    .lb-arrow.next { right:20px; }
    #pdp-lightbox .lb-close { position:absolute; top:16px; right:16px;
        background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
        backdrop-filter:blur(8px); color:#fff; width:40px; height:40px;
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        cursor:pointer; font-size:16px; transition:background .2s; }
    #pdp-lightbox .lb-close:hover { background:rgba(255,255,255,.25); }
    #pdp-lightbox .lb-counter { position:absolute; bottom:18px; left:50%;
        transform:translateX(-50%); color:rgba(255,255,255,.8); font-size:13px;
        background:rgba(0,0,0,.4); px:12px; padding:4px 16px; border-radius:99px; }

    /* ── Tabs ──────────────────────────────────────────────────── */
    .pdp-tab-btn { border-bottom: 2px solid transparent; color: var(--fe-text-muted); }
    .pdp-tab-btn.active { border-bottom-color: var(--fe-primary); color: var(--fe-primary); font-weight:600; }

    /* ── Spec table ────────────────────────────────────────────── */
    .spec-row:nth-child(even) { background: var(--fe-surface-soft); }

    /* ── Sticky purchase panel ─────────────────────────────────── */
    @media (min-width: 1024px) {
        .pdp-sticky { position: sticky; top: 80px; }
        /* Ensure the parent grid row allows sticky children */
        .pdp-grid-row { align-items: start; }
    }
</style>
@endpush

@section('content')
@php
    $isProduct      = $listing->listing_type === 'product';
    $supplierProfile = $listing->supplierAccount?->supplierProfile;
    $globalTiers    = $listing->allTierPrices->whereNull('listing_variant_id');
    $hasVariants    = $isProduct && $listing->variants->isNotEmpty();
    $hasAnyTiers    = $listing->allTierPrices->isNotEmpty();

    // Build gallery — primary image first
    $gallery = $listing->getMedia('gallery');
    $primaryMediaId = $listing->primary_image_media_id;
    if ($primaryMediaId && $gallery->count() > 1) {
        $gallery = $gallery->sortByDesc(fn($m) => $m->id == $primaryMediaId)->values();
    }
    $galleryUrls = $gallery->map(fn($m) => $m->getUrl())->values()->toArray();
    $firstUrl    = $galleryUrls[0] ?? null;
    $galleryCount = count($galleryUrls);
@endphp

{{-- ══════════════════════════════════════════════════════════
     LIGHTBOX (full-screen, shared for all gallery images)
══════════════════════════════════════════════════════════ --}}
<div id="pdp-lightbox" role="dialog" aria-modal="true" aria-label="Product image lightbox" style="display:none;">
    <button class="lb-close" id="lb-close" title="Close (Esc)"><i class="fa-solid fa-xmark"></i></button>
    <button class="lb-arrow prev" id="lb-prev" title="Previous"><i class="fa-solid fa-chevron-left"></i></button>
    <img id="lb-img" src="" alt="{{ $listing->name }}" loading="lazy">
    <button class="lb-arrow next" id="lb-next" title="Next"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="lb-counter"><span id="lb-cur">1</span> / <span id="lb-total">{{ $galleryCount }}</span></div>
</div>

<div class="fe-container py-6 sm:py-8">
    {{-- Breadcrumb --}}
    <x-frontend::navigation.breadcrumbs :items="[
        ($isProduct ? 'Products' : 'Services') => route($isProduct ? 'frontend.products.index' : 'frontend.services.index'),
        $listing->name => null,
    ]" />

    {{-- ══ MAIN PRODUCT LAYOUT ══════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-4 pdp-grid-row">

        {{-- ── LEFT COL: Gallery (thumbnails + main image) ─────── --}}
        <div class="lg:col-span-5 xl:col-span-4">
            @if($firstUrl)
                <div class="flex gap-3" x-data>
                    {{-- Vertical thumbnail strip --}}
                    @if($galleryCount > 1)
                        <div class="hidden sm:flex flex-col gap-2 w-16 shrink-0" id="thumb-strip">
                            @foreach($galleryUrls as $idx => $url)
                                <button
                                    type="button"
                                    onclick="pdpSetImage({{ $idx }})"
                                    id="thumb-{{ $idx }}"
                                    class="pdp-thumb {{ $idx === 0 ? 'active' : '' }} aspect-square rounded-xl border-2 overflow-hidden bg-white shrink-0 focus:outline-none"
                                    style="border-color:var(--fe-border);"
                                    title="Photo {{ $idx + 1 }}">
                                    <img src="{{ $url }}" alt="{{ $listing->name }} {{ $idx + 1 }}"
                                         class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Main image container --}}
                    <div class="flex-1 relative">
                        {{-- Main image --}}
                        <div class="relative rounded-2xl border overflow-hidden bg-white group cursor-zoom-in"
                             style="border-color:var(--fe-border); aspect-ratio:1/1;"
                             onclick="pdpOpenLightbox(currentPdpIdx)">
                            <img id="pdp-main-img"
                                 src="{{ $firstUrl }}"
                                 alt="{{ $listing->name }}"
                                 class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105">

                            {{-- Prev / Next arrows --}}
                            @if($galleryCount > 1)
                                <button type="button"
                                        onclick="event.stopPropagation(); pdpSetImage(currentPdpIdx - 1)"
                                        class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-sm transition-all opacity-0 group-hover:opacity-100"
                                        style="background:rgba(255,255,255,.9);box-shadow:0 2px 8px rgba(0,0,0,.12);color:var(--fe-text);"
                                        title="Previous">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button"
                                        onclick="event.stopPropagation(); pdpSetImage(currentPdpIdx + 1)"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-sm transition-all opacity-0 group-hover:opacity-100"
                                        style="background:rgba(255,255,255,.9);box-shadow:0 2px 8px rgba(0,0,0,.12);color:var(--fe-text);"
                                        title="Next">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>

                                {{-- Counter badge --}}
                                <div class="absolute bottom-2.5 right-3 pointer-events-none">
                                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-black/45 text-white backdrop-blur-sm">
                                        <span id="pdp-counter-cur">1</span> / {{ $galleryCount }}
                                    </span>
                                </div>
                            @endif

                            {{-- Zoom hint --}}
                            <div class="absolute top-3 right-3 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-black/40 text-white backdrop-blur-sm">
                                    <i class="fa-solid fa-magnifying-glass-plus text-[9px] mr-0.5"></i> Click to zoom
                                </span>
                            </div>
                        </div>

                        {{-- Mobile horizontal thumbnail strip --}}
                        @if($galleryCount > 1)
                            <div class="sm:hidden flex gap-2 mt-2 overflow-x-auto pb-1" id="thumb-strip-mobile">
                                @foreach($galleryUrls as $idx => $url)
                                    <button type="button"
                                            onclick="pdpSetImage({{ $idx }})"
                                            id="thumb-m-{{ $idx }}"
                                            class="pdp-thumb {{ $idx === 0 ? 'active' : '' }} aspect-square rounded-xl border-2 overflow-hidden bg-white shrink-0 w-14 focus:outline-none"
                                            style="border-color:var(--fe-border);">
                                        <img src="{{ $url }}" alt="" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-2xl border flex items-center justify-center"
                     style="aspect-ratio:1/1;border-color:var(--fe-border);background:var(--fe-surface-soft);">
                    <div class="text-center">
                        <i class="fa-solid {{ $isProduct ? 'fa-box' : 'fa-briefcase' }} text-6xl mb-3"
                           style="color:var(--fe-text-subtle);"></i>
                        <p class="text-sm" style="color:var(--fe-text-subtle);">No image uploaded</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── CENTER COL: Product Info ─────────────────────────── --}}
        <div class="lg:col-span-4 xl:col-span-5">
            {{-- Badges --}}
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <x-frontend::common.badge>{{ $isProduct ? 'Product' : 'Service' }}</x-frontend::common.badge>
                @if($supplierProfile)
                    <x-frontend::common.badge variant="verified">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier
                    </x-frontend::common.badge>
                @endif
                @if($listing->is_featured)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-star text-[10px]"></i> Featured
                    </span>
                @endif
            </div>

            {{-- Title --}}
            <h1 class="text-2xl sm:text-[26px] font-bold tracking-tight leading-snug"
                style="font-family:var(--font-display);color:var(--fe-text);">
                {{ $listing->name }}
            </h1>

            {{-- Meta row --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm" style="color:var(--fe-text-muted);">
                @if($listing->brand)
                    <span>Brand: <strong style="color:var(--fe-text);">{{ $listing->brand->name }}</strong></span>
                @endif
                @if($listing->sku)
                    <span>SKU: <code class="text-xs font-mono">{{ $listing->sku }}</code></span>
                @endif
                @if($listing->mainCategory)
                    <span>{{ $listing->mainCategory->name }}</span>
                @endif
            </div>

            {{-- Short description --}}
            @if($listing->short_description)
                <p class="mt-4 text-sm leading-relaxed" style="color:var(--fe-text-muted);">
                    {{ $listing->short_description }}
                </p>
            @endif

            <hr class="my-5" style="border-color:var(--fe-border);">

            {{-- Price --}}
            <div class="mb-4">
                @if($listing->pricing_type === 'fixed' && $listing->base_price)
                    <div class="flex items-baseline gap-3 flex-wrap">
                        <span class="text-3xl font-bold" style="color:var(--fe-text);">
                            {{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}
                        </span>
                        @if($listing->unit)
                            <span class="text-sm" style="color:var(--fe-text-muted);">per {{ $listing->unit->symbol }}</span>
                        @endif
                        @if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price)
                            <span class="text-lg line-through" style="color:var(--fe-text-subtle);">
                                {{ $listing->currency_code }} {{ number_format($listing->compare_at_price, 2) }}
                            </span>
                            <span class="text-sm font-semibold px-2 py-0.5 rounded-lg" style="background:var(--fe-success-soft,#d1fae5);color:#065f46;">
                                Save {{ number_format((($listing->compare_at_price - $listing->base_price) / $listing->compare_at_price) * 100) }}%
                            </span>
                        @endif
                    </div>
                    @if($listing->sales_mode === 'rfq_only')
                        <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Price shown is indicative. Submit an RFQ for confirmed pricing.</p>
                    @endif
                @else
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold" style="color:var(--fe-primary);">Request for Quote</span>
                        <i class="fa-solid fa-file-invoice-dollar text-sm" style="color:var(--fe-primary);"></i>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Pricing is negotiated per order. Submit an RFQ to receive a formal quote.</p>
                @endif
            </div>

            {{-- Quick stats chips --}}
            <div class="flex flex-wrap gap-2 mb-5">
                @if($isProduct && $listing->productDetail?->stock_status)
                    @php $ss = $listing->productDetail->stock_status; @endphp
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <span class="w-2 h-2 rounded-full {{ $ss === 'in_stock' ? 'bg-green-500' : ($ss === 'limited' ? 'bg-amber-500' : 'bg-red-400') }}"></span>
                        {{ str_replace('_', ' ', ucfirst($ss)) }}
                    </div>
                @endif
                @if($listing->min_order_quantity)
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-solid fa-boxes-stacking text-[11px]" style="color:var(--fe-text-muted);"></i>
                        MOQ: {{ rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.') }}{{ $listing->unit ? ' '.$listing->unit->symbol : '' }}
                    </div>
                @endif
                @if($isProduct && $listing->productDetail?->lead_time_days)
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-regular fa-clock text-[11px]" style="color:var(--fe-text-muted);"></i>
                        {{ $listing->productDetail->lead_time_days }}-day lead time
                    </div>
                @endif
                @if(!$isProduct && $listing->serviceDetail?->service_mode)
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-solid fa-display text-[11px]" style="color:var(--fe-text-muted);"></i>
                        {{ ucfirst($listing->serviceDetail->service_mode) }}
                    </div>
                @endif
            </div>

            {{-- Variants --}}
            @if($hasVariants)
                <div class="mb-5">
                    <x-frontend::marketplace.variant-selector :variants="$listing->variants" />
                </div>
            @endif

            {{-- Tier pricing --}}
            @if($hasAnyTiers)
                <div class="mb-5">
                    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">
                        <i class="fa-solid fa-tags text-xs mr-1" style="color:var(--fe-primary);"></i>
                        Volume Discount Tiers
                    </p>
                    @if($globalTiers->isNotEmpty())
                        <x-frontend::marketplace.tier-pricing-table :tiers="$globalTiers" :currency="$listing->currency_code" />
                    @else
                        <x-frontend::marketplace.tier-pricing-table :tiers="$listing->allTierPrices" :currency="$listing->currency_code" />
                    @endif
                </div>
            @endif

            {{-- CTA Buttons (visible on mobile, also in sticky panel on desktop) --}}
            <div class="flex flex-col gap-2 lg:hidden mt-2">
                @auth
                    <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}"
                       class="fe-btn-primary fe-focus-ring block text-center px-4 py-3 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                    </a>
                @else
                    <a href="{{ route('frontend.handoff.request-quote-listing', $listing->slug) }}"
                       class="fe-btn-primary fe-focus-ring block text-center px-4 py-3 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                    </a>
                @endauth
                <button type="button" @click="$dispatch('open-inquiry-listing')"
                        class="fe-focus-ring block w-full text-center px-4 py-3 rounded-xl text-sm font-semibold border"
                        style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                    <i class="fa-regular fa-comment-dots mr-1.5"></i> Contact Supplier
                </button>
                <x-frontend::marketplace.compare-button :listing="$listing" style="text" />
            </div>

        </div>

        {{-- ── RIGHT COL: Sticky Purchase Panel ───────────────── --}}
        <div class="hidden lg:block lg:col-span-3">
            <div class="pdp-sticky space-y-4">
                {{-- Purchase Card --}}
                <div class="fe-card rounded-2xl p-5 space-y-4">
                    @if($listing->pricing_type === 'fixed' && $listing->base_price)
                        <div>
                            <p class="text-2xl font-bold" style="color:var(--fe-text);">
                                {{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}
                            </p>
                            @if($listing->unit)
                                <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">per {{ $listing->unit->symbol }}</p>
                            @endif
                            @if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price)
                                <p class="text-sm line-through mt-0.5" style="color:var(--fe-text-subtle);">
                                    {{ $listing->currency_code }} {{ number_format($listing->compare_at_price, 2) }}
                                </p>
                            @endif
                        </div>
                    @else
                        <div>
                            <p class="text-lg font-bold" style="color:var(--fe-primary);">Price on Request</p>
                            <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">Submit an RFQ for pricing</p>
                        </div>
                    @endif

                    {{-- Quick info --}}
                    <div class="space-y-2 text-sm">
                        @if($isProduct && $listing->productDetail?->stock_status)
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Availability</span>
                                <span class="font-medium" style="color:var(--fe-text);">
                                    {{ str_replace('_', ' ', ucfirst($listing->productDetail->stock_status)) }}
                                </span>
                            </div>
                        @endif
                        @if($listing->min_order_quantity)
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Min. Order</span>
                                <span class="font-medium" style="color:var(--fe-text);">
                                    {{ rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.') }}
                                    {{ $listing->unit?->symbol }}
                                </span>
                            </div>
                        @endif
                        @if($isProduct && $listing->productDetail?->lead_time_days)
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Lead Time</span>
                                <span class="font-medium" style="color:var(--fe-text);">{{ $listing->productDetail->lead_time_days }} days</span>
                            </div>
                        @endif
                    </div>

                    <hr style="border-color:var(--fe-border);">

                    {{-- CTAs --}}
                    <div class="space-y-2">
                        @auth
                            <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}"
                               class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-xl text-sm font-semibold">
                                <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                            </a>
                        @else
                            <a href="{{ route('frontend.handoff.request-quote-listing', $listing->slug) }}"
                               class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-xl text-sm font-semibold">
                                <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                            </a>
                        @endauth

                        <button type="button" @click="$dispatch('open-inquiry-listing')"
                                class="fe-focus-ring block w-full text-center px-4 py-2.5 rounded-xl text-sm font-semibold border"
                                style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                            <i class="fa-regular fa-comment-dots mr-1.5"></i> Contact Supplier
                        </button>

                        <a href="{{ auth()->check() ? route('buyer.saved-items.toggle') : route('frontend.handoff.save-listing', $listing->slug) }}"
                           @if(auth()->check())
                           onclick="event.preventDefault(); document.getElementById('fe-save-listing-form').submit();"
                           @endif
                           class="fe-focus-ring block w-full text-center px-4 py-2 rounded-xl text-sm font-medium transition-colors hover:opacity-80"
                           style="color:var(--fe-text-muted);">
                            <i class="fa-regular fa-bookmark mr-1.5"></i> Save Listing
                        </a>
                        <x-frontend::marketplace.compare-button :listing="$listing" style="text" />
                        @auth
                            <form id="fe-save-listing-form" method="POST"
                                  action="{{ route('buyer.saved-items.toggle') }}" class="hidden">
                                @csrf
                                <input type="hidden" name="type" value="listing">
                                <input type="hidden" name="id" value="{{ $listing->id }}">
                            </form>
                        @endauth
                    </div>

                    {{-- Trust badges --}}
                    <div class="grid grid-cols-3 gap-2 pt-2 text-center text-[10px]" style="color:var(--fe-text-muted);">
                        <div><i class="fa-solid fa-shield-halved block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Secure</div>
                        <div><i class="fa-solid fa-headset block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Support</div>
                        <div><i class="fa-solid fa-certificate block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Verified</div>
                    </div>
                </div>

                {{-- Supplier Card --}}
                @if($supplierProfile)
                    <div class="fe-card rounded-2xl p-5">
                        <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:var(--fe-text-muted);">Sold by</p>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-11 h-11 rounded-xl flex items-center justify-center text-base font-bold shrink-0"
                                  style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
                                {{ strtoupper(substr($supplierProfile->display_name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:var(--fe-text);">{{ $supplierProfile->display_name }}</p>
                                <x-frontend::marketplace.rating-summary
                                    :rating="$supplierProfile->rating"
                                    :count="$supplierProfile->reviews_count ?? 0" />
                            </div>
                        </div>
                        @if($supplierProfile->city || $supplierProfile->country)
                            <p class="text-xs mb-3" style="color:var(--fe-text-muted);">
                                <i class="fa-solid fa-location-dot text-[10px] mr-1"></i>
                                {{ collect([$supplierProfile->city?->name, $supplierProfile->country?->name])->filter()->implode(', ') }}
                            </p>
                        @endif
                        <a href="{{ route('frontend.suppliers.show', $supplierProfile->slug) }}"
                           class="fe-focus-ring block text-center px-4 py-2 rounded-xl text-sm font-semibold border"
                           style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                            View Storefront
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- ══ DETAILS & SIMILAR PRODUCTS ════════════════════════════
         A separate full-width section below the top image/buy-box
         area: tabs on the left, "Similar Product" list on the right. --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-10">

        {{-- ── LEFT: Description / Specs / Terms tabs ──────────── --}}
        <div class="lg:col-span-8" id="pdp-tabs" x-data="{ tab: 'desc' }">
            <div class="flex items-center gap-0 border-b overflow-x-auto" style="border-color:var(--fe-border);">
                @foreach(($isProduct
                    ? ['desc' => 'Description', 'specs' => 'Specifications', 'terms' => 'Warranty & Support']
                    : ['desc' => 'Description', 'specs' => 'Service Details', 'terms' => 'Terms & Coverage']
                ) as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'active' : ''"
                            class="pdp-tab-btn px-4 py-2.5 text-sm whitespace-nowrap transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="py-5 text-sm leading-relaxed" style="color:var(--fe-text-muted);">

                {{-- Description --}}
                <div x-show="tab === 'desc'">
                    {!! nl2br(e($listing->description ?? 'No description provided.')) !!}
                </div>

                {{-- Specifications --}}
                <div x-show="tab === 'specs'" x-cloak>
                    @if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
                        {{-- StarTech-style: each group = one table, group name = full-width header row --}}
                        <div class="space-y-4">
                            @foreach($groupedSpecifications as $group)
                                <table class="w-full text-sm border-collapse" style="border:1px solid var(--fe-border);border-radius:8px;overflow:hidden;">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="px-4 py-2.5 text-left text-sm font-bold"
                                                style="background:var(--fe-surface-soft);color:var(--fe-primary);border-bottom:1px solid var(--fe-border);">
                                                {{ $group['group_name'] }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['items'] as $item)
                                            <tr class="border-b" style="border-color:var(--fe-border);">
                                                <td class="px-4 py-2.5 w-2/5" style="color:var(--fe-text-muted);font-size:13px;">
                                                    {{ $item->attribute?->name }}
                                                    @if($item->attribute?->unit)
                                                        <span class="opacity-60 text-xs">
                                                            ({{ $item->attribute->unit->symbol ?? $item->attribute->unit->name }})
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5" style="color:var(--fe-text);font-size:13px;font-weight:500;">
                                                    @if($item->attribute?->input_type === 'color' && $item->attributeValue?->color_hex)
                                                        <span class="inline-flex items-center gap-2">
                                                            <span class="w-3.5 h-3.5 rounded-full border border-gray-300 inline-block shadow-xs"
                                                                  style="background-color:{{ $item->attributeValue->color_hex }};"></span>
                                                            {{ $item->formattedValue() }}
                                                        </span>
                                                    @else
                                                        {{ $item->formattedValue() }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                    @elseif($listing->attributeValues->isNotEmpty())
                        <table class="w-full text-sm border-collapse" style="border:1px solid var(--fe-border);border-radius:8px;overflow:hidden;">
                            <tbody>
                                @foreach($listing->attributeValues as $value)
                                    <tr class="border-b" style="border-color:var(--fe-border);">
                                        <td class="px-4 py-2.5 w-2/5" style="color:var(--fe-text-muted);font-size:13px;">{{ $value->attribute?->name }}</td>
                                        <td class="px-4 py-2.5" style="color:var(--fe-text);font-size:13px;font-weight:500;">{{ $value->formattedValue() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="color:var(--fe-text-muted);">No additional specifications listed for this {{ $isProduct ? 'product' : 'service' }}.</p>
                    @endif
                </div>

                {{-- Warranty / Terms --}}
                <div x-show="tab === 'terms'" x-cloak>
                    @if($isProduct)
                        @if($listing->productDetail?->warranty_terms)
                            <p class="mb-3"><strong style="color:var(--fe-text);">Warranty:</strong> {{ $listing->productDetail->warranty_terms }}</p>
                        @endif
                        @if($listing->productDetail?->support_terms)
                            <p class="mb-3"><strong style="color:var(--fe-text);">Support:</strong> {{ $listing->productDetail->support_terms }}</p>
                        @endif
                        @if(!$listing->productDetail?->warranty_terms && !$listing->productDetail?->support_terms)
                            <p>No warranty or support terms specified.</p>
                        @endif
                    @else
                        @if($listing->serviceDetail?->service_terms)
                            <p class="mb-3"><strong style="color:var(--fe-text);">Service Terms:</strong> {{ $listing->serviceDetail->service_terms }}</p>
                        @endif
                        @if($listing->serviceDetail?->support_terms)
                            <p class="mb-3"><strong style="color:var(--fe-text);">Support:</strong> {{ $listing->serviceDetail->support_terms }}</p>
                        @endif
                        @if(!$listing->serviceDetail?->service_terms && !$listing->serviceDetail?->support_terms)
                            <p>No service terms or coverage details specified.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Similar Product ───────────────────────────── --}}
        @if($related->isNotEmpty())
            <div class="lg:col-span-4">
                <div class="fe-card rounded-2xl p-5">
                    <p class="text-sm font-bold mb-4" style="color:var(--fe-text);">Similar Product</p>
                    <div class="space-y-4">
                        @foreach($related as $item)
                            @php($itemImg = $item->getMedia('gallery')->first())
                            <a href="{{ route('frontend.listings.show', $item->slug) }}"
                               class="flex items-start gap-3 group {{ !$loop->first ? 'pt-4 border-t' : '' }}"
                               @if(!$loop->first) style="border-color:var(--fe-border);" @endif>
                                <div class="w-16 h-16 rounded-lg border overflow-hidden bg-white shrink-0 flex items-center justify-center"
                                     style="border-color:var(--fe-border);">
                                    @if($itemImg)
                                        <img src="{{ $itemImg->getUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-contain p-1">
                                    @else
                                        <i class="fa-solid fa-box text-xl" style="color:var(--fe-text-subtle);"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium leading-snug line-clamp-2 transition-colors group-hover:opacity-80"
                                       style="color:var(--fe-text);">
                                        {{ $item->name }}
                                    </p>
                                    <p class="text-sm font-bold mt-1" style="color:var(--fe-primary);">
                                        @if($item->pricing_type === 'fixed' && $item->base_price)
                                            {{ $item->currency_code }} {{ number_format($item->base_price, 2) }}
                                        @else
                                            Request Quote
                                        @endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- /details grid --}}

</div>{{-- /fe-container --}}

{{-- Inquiry Modal --}}
@if($supplierProfile)
    <x-frontend::marketplace.inquiry-modal
        trigger-id="listing"
        :action="route('frontend.inquiries.listing', $listing->slug)"
        :context="$supplierProfile->display_name"
    />
@endif

@auth
    @if(request('save_intent'))
        @push('scripts')
            <script>document.getElementById('fe-save-listing-form')?.submit();</script>
        @endpush
    @endif
@endauth

@if(request('contact') && $supplierProfile)
    @push('scripts')
        <script>window.dispatchEvent(new CustomEvent('open-inquiry-listing'));</script>
    @endpush
@endif

{{-- ════════════════════════════════════════════════════════════
     GALLERY JS — Prev/Next, Thumbnail sync, Lightbox
════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
(function() {
    const IMAGES = @json($galleryUrls);
    let currentPdpIdx = 0;

    window.currentPdpIdx = currentPdpIdx;

    window.pdpSetImage = function(idx) {
        if (IMAGES.length === 0) return;
        // Wrap around
        if (idx < 0) idx = IMAGES.length - 1;
        if (idx >= IMAGES.length) idx = 0;

        currentPdpIdx = idx;
        window.currentPdpIdx = idx;

        // Update main image
        const mainImg = document.getElementById('pdp-main-img');
        if (mainImg) {
            mainImg.style.opacity = '0.6';
            mainImg.src = IMAGES[idx];
            mainImg.onload = () => { mainImg.style.opacity = '1'; };
        }

        // Update counter
        const cur = document.getElementById('pdp-counter-cur');
        if (cur) cur.textContent = idx + 1;

        // Update thumbnail active states — desktop
        document.querySelectorAll('[id^="thumb-"]').forEach((el) => {
            const elIdx = parseInt(el.id.replace('thumb-m-', '').replace('thumb-', ''));
            if (!isNaN(elIdx)) {
                el.classList.toggle('active', elIdx === idx);
            }
        });
    };

    // ── Lightbox ───────────────────────────────────────────
    const lightbox  = document.getElementById('pdp-lightbox');
    const lbImg     = document.getElementById('lb-img');
    const lbCur     = document.getElementById('lb-cur');
    const lbTotal   = document.getElementById('lb-total');

    function updateLightbox(idx) {
        currentPdpIdx = idx;
        window.currentPdpIdx = idx;
        if (lbImg) lbImg.src = IMAGES[idx];
        if (lbCur) lbCur.textContent = idx + 1;
    }

    window.pdpOpenLightbox = function(idx) {
        if (IMAGES.length === 0) return;
        updateLightbox(idx);
        lightbox?.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    function closeLightbox() {
        lightbox?.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('lb-close')?.addEventListener('click', closeLightbox);
    document.getElementById('lb-prev')?.addEventListener('click', () => {
        let i = currentPdpIdx - 1;
        if (i < 0) i = IMAGES.length - 1;
        updateLightbox(i);
    });
    document.getElementById('lb-next')?.addEventListener('click', () => {
        let i = currentPdpIdx + 1;
        if (i >= IMAGES.length) i = 0;
        updateLightbox(i);
    });

    lightbox?.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox?.classList.contains('open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') {
            let i = currentPdpIdx - 1;
            if (i < 0) i = IMAGES.length - 1;
            updateLightbox(i);
        }
        if (e.key === 'ArrowRight') {
            let i = currentPdpIdx + 1;
            if (i >= IMAGES.length) i = 0;
            updateLightbox(i);
        }
    });
})();
</script>
@endpush

@endsection
