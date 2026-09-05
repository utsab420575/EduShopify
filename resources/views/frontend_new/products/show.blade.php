@extends('frontend_new.layouts.app')

@section('title', $listing->name . ' – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')

{{-- ─────────────────────────── BREADCRUMB ─────────────────────────── --}}
<div class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('v2.home') }}" class="hover:text-gray-900">← Marketplace</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">{{ $listing->name }}</span>
  </div>
</div>

{{-- ─────────────────────────── MAIN ─────────────────────────── --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-7">
  <div class="flex flex-col lg:flex-row gap-7 items-start">

    {{-- ══════════════ LEFT COLUMN ══════════════ --}}
    <div class="w-full lg:flex-1 flex flex-col gap-5 min-w-0">

      {{-- ── Gallery ── --}}
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="relative">
          @if($images->isNotEmpty())
            <img id="main-img" src="{{ $images->first()->getUrl() }}" alt="{{ $listing->name }}" class="w-full h-[340px] sm:h-[400px] object-cover" />
          @else
            <div id="main-img" class="w-full h-[340px] sm:h-[400px] bg-gray-100 flex items-center justify-center text-gray-400">No image available</div>
          @endif
        </div>
        @if($images->count() > 1)
          <div class="flex gap-2 p-3 border-t border-gray-100">
            @foreach($images as $img)
              <div class="thumb {{ $loop->first ? 'active' : '' }} w-16 h-14" onclick="switchImg(this,'{{ $img->getUrl() }}')">
                <img src="{{ $img->getUrl() }}" class="w-full h-full object-cover" />
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- ── Product Description ── --}}
      <div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <h2 class="text-[15px] font-semibold text-gray-900">Product Description</h2>
        </div>
        <div class="text-sm text-gray-600 leading-relaxed space-y-3">
          {!! nl2br(e($listing->description ?? $listing->short_description ?? 'No description provided.')) !!}
        </div>
      </div>

      {{-- ── Specifications ── --}}
      @if($listing->attributeValues->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
          <h2 class="text-[15px] font-semibold text-gray-900 mb-4">Specifications</h2>
          <div class="spec-table">
            @foreach($listing->attributeValues as $index => $spec)
              <div class="spec-row {{ $index % 2 === 1 ? 'highlight' : '' }}">
                <span class="spec-cell-label">{{ $spec->attribute?->name }}@if($spec->attribute?->unit) ({{ $spec->attribute->unit->symbol ?? $spec->attribute->unit->name }})@endif</span>
                <span class="spec-cell-value">{{ $spec->formattedValue() }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- ── Buyer Protection ── --}}
      <div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
        <h2 class="text-[15px] font-semibold text-gray-900 mb-5">Buyer Protection</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
          <div>
            <div class="protect-icon"><svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div>
            <p class="text-xs font-semibold text-gray-800 mb-0.5">Verified Supplier</p>
            <p class="text-[11px] text-gray-400 leading-snug">Identity &amp; credentials confirmed</p>
          </div>
          <div>
            <div class="protect-icon"><svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1.5"/><path d="m16 8 5 3v5h-5V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
            <p class="text-xs font-semibold text-gray-800 mb-0.5">On-Time Delivery</p>
            <p class="text-[11px] text-gray-400 leading-snug">96%+ on-time shipment rate</p>
          </div>
          <div>
            <div class="protect-icon"><svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
            <p class="text-xs font-semibold text-gray-800 mb-0.5">Quality Assured</p>
            <p class="text-[11px] text-gray-400 leading-snug">Products meet platform standards</p>
          </div>
          <div>
            <div class="protect-icon"><svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <p class="text-xs font-semibold text-gray-800 mb-0.5">After-Sales Support</p>
            <p class="text-[11px] text-gray-400 leading-snug">Dedicated support post-purchase</p>
          </div>
        </div>
      </div>

      {{-- ── Customer Reviews ── --}}
      <div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
        <h2 class="text-[15px] font-semibold text-gray-900 mb-5">Customer Reviews</h2>

        {{--
          The static reference also shows a Quality/Value/Delivery/Support
          sub-rating breakdown next to the score. The Review model only
          stores one overall rating per review — no such sub-metrics exist
          — so, rather than inventing per-dimension numbers for a real
          product, that breakdown is omitted here and only the real
          aggregate score is shown.
        --}}
        <div class="flex flex-col sm:flex-row gap-6 mb-6">
          <div class="shrink-0 text-center sm:text-left">
            <p class="text-5xl font-bold text-gray-900 leading-none">{{ number_format((float) $listing->product_rating, 1) }}</p>
            <div class="flex items-center justify-center sm:justify-start gap-0.5 mt-2">
              @for($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= round($listing->product_rating) ? 'star-filled' : 'star-empty' }} text-lg">★</span>
              @endfor
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $listing->product_reviews_count }} Reviews</p>
          </div>
        </div>

        <div class="border-t border-gray-100"></div>

        @forelse($listing->productReviews as $review)
          <div class="py-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div class="flex items-start justify-between mb-2">
              <div class="flex items-center gap-2.5">
                <div class="reviewer-avatar bg-emerald-100 text-emerald-700">{{ strtoupper(substr($review->buyerAccount?->display_name ?? '?', 0, 1)) }}</div>
                <div>
                  <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $review->buyerAccount?->display_name ?? 'Anonymous Buyer' }}</p>
                  @if($review->buyerAccount?->buyerProfile?->organization_name)
                    <p class="text-xs text-gray-400">{{ $review->buyerAccount->buyerProfile->organization_name }}</p>
                  @endif
                </div>
              </div>
              <span class="text-xs text-gray-400">{{ $review->published_at?->format('M Y') }}</span>
            </div>
            <div class="flex items-center gap-0.5 mb-2">
              @for($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }} text-sm">★</span>
              @endfor
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $review->comment }}</p>
          </div>
        @empty
          <p class="text-sm text-gray-400 py-5">No reviews yet for this product.</p>
        @endforelse
      </div>

    </div>{{-- /LEFT --}}

    {{-- ══════════════ RIGHT SIDEBAR ══════════════ --}}
    <div class="w-full lg:w-[340px] xl:w-[360px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">

      {{-- ── Product Info Card ── --}}
      <div class="sidebar-card">
        <h1 class="text-[17px] font-bold text-gray-900 leading-snug mb-1">{{ $listing->name }}</h1>
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
          <span>by
            <a href="{{ \Illuminate\Support\Facades\Route::has('v2.suppliers.show') && $supplierProfile ? route('v2.suppliers.show', $supplierProfile->slug) : '#' }}" class="text-emerald-600 hover:underline font-semibold">
              {{ $supplierProfile?->display_name ?? 'Unknown Supplier' }}
            </a>
          </span>
          @if($listing->sku)
            <span class="bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded text-[11px] tracking-wide">{{ $listing->sku }}</span>
          @endif
        </div>

        <div class="flex items-center gap-2 mb-4">
          <div class="flex items-center gap-0.5">
            @for($i = 1; $i <= 5; $i++)
              <span class="{{ $i <= round($listing->product_rating) ? 'star-filled' : 'star-empty' }} text-base">★</span>
            @endfor
          </div>
          <span class="text-sm font-semibold text-gray-800">{{ number_format((float) $listing->product_rating, 1) }}</span>
          <span class="text-sm text-gray-400">· {{ $listing->product_reviews_count }} reviews</span>
        </div>

        <div class="border-t border-gray-100 mb-4"></div>

        <div class="bg-emerald-50 border border-emerald-100 rounded-lg px-4 py-3 mb-4">
          <p class="text-xs text-emerald-700 font-medium mb-1">Price Range</p>
          <p class="text-[24px] font-extrabold text-emerald-600 leading-none mb-0.5">
            @if($priceMin == $priceMax)
              ${{ number_format((float) $priceMin, 2) }}
            @else
              ${{ number_format((float) $priceMin, 2) }} – ${{ number_format((float) $priceMax, 2) }}
            @endif
            <span class="text-[15px] font-semibold">{{ $listing->unit?->symbol ?? 'each' }}</span>
          </p>
          <p class="text-xs text-gray-400 mt-1">{{ $listing->currency_code ?? 'USD' }} · Contact for bulk pricing</p>
        </div>

        <div class="flex flex-col gap-2.5 mb-5">
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            Verified Supplier
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1.5"/><path d="m16 8 5 3v5h-5V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Fast Worldwide Delivery
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Secure Purchase Terms
          </div>
        </div>

        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-md text-sm flex items-center justify-center gap-2 mb-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          Request Quotation
        </button>
        <button class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-2.5 rounded-md text-sm flex items-center justify-center gap-2 mb-4 hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Contact Supplier
        </button>

        <div class="flex items-center gap-2">
          <button class="flex-1 flex items-center justify-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 py-2 border border-gray-200 hover:border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Save
          </button>
          <button class="flex-1 flex items-center justify-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 py-2 border border-gray-200 hover:border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Share
          </button>
        </div>
      </div>

      {{-- ── Supplied By Card ── --}}
      @if($supplierProfile)
        <div class="sidebar-card">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Supplied By</p>
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded bg-blue-100 text-blue-700 font-bold text-base flex items-center justify-center shrink-0">{{ strtoupper(substr($supplierProfile->display_name, 0, 1)) }}</div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ $supplierProfile->display_name }}</p>
              <div class="flex items-center gap-1 text-xs text-emerald-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                Verified Supplier
              </div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2 mb-4 py-3 border-y border-gray-100">
            <div class="stat-box">
              <p class="val">{{ $supplierProfile->quotation_response_rate !== null ? round($supplierProfile->quotation_response_rate).'%' : '—' }}</p>
              <p class="lbl">Response</p>
            </div>
            <div class="stat-box">
              <p class="val">{{ $dealsCount }}+</p>
              <p class="lbl">Deals</p>
            </div>
            <div class="stat-box">
              <p class="val">{{ $yearsActive ?? '—' }}{{ $yearsActive ? '+' : '' }}</p>
              <p class="lbl">Years</p>
            </div>
          </div>

          <a href="{{ \Illuminate\Support\Facades\Route::has('v2.suppliers.show') ? route('v2.suppliers.show', $supplierProfile->slug) : '#' }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Browse Suppliers →</a>
        </div>
      @endif

      {{-- ── Why Edushopify Card ── --}}
      <div class="sidebar-card">
        <p class="text-sm font-semibold text-gray-900 mb-3">Why Edushopify?</p>
        <div class="why-row"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Verified &amp; trusted suppliers</div>
        <div class="why-row"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Secure quotation process</div>
        <div class="why-row"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Dedicated buyer support</div>
        <div class="why-row"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Compare multiple quotes</div>
        <div class="why-row" style="margin-bottom:0"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Safe payment terms</div>
      </div>

      {{-- ── Fast Response Card ── --}}
      <div class="fast-response">
        <div class="flex items-start gap-2.5">
          <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          <div>
            <p class="text-sm font-semibold text-emerald-700 mb-0.5">Fast Response</p>
            <p class="text-xs text-gray-600 leading-relaxed">Suppliers on Edushopify respond to quote requests within <strong>24 hours</strong> on average.</p>
          </div>
        </div>
      </div>

    </div>{{-- /RIGHT SIDEBAR --}}

  </div>{{-- /two-col --}}

  {{-- ─────────────────────────── YOU MAY ALSO LIKE ─────────────────────────── --}}
  @if($relatedProducts->isNotEmpty())
    <div class="mt-10">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-[17px] font-bold text-gray-900">You May Also Like</h2>
        <a href="{{ route('v2.home') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View all products →</a>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($relatedProducts as $related)
          <div class="product-card">
            <div class="relative">
              @if($related->primaryImage)
                <img src="{{ $related->primaryImage->getUrl() }}" alt="{{ $related->name }}" class="w-full h-36 object-cover" />
              @else
                <div class="w-full h-36 bg-gray-100 flex items-center justify-center text-gray-400 text-xs">No image</div>
              @endif
              @if($related->is_active)
                <span class="in-stock absolute top-2 left-2">In Stock</span>
              @endif
            </div>
            <div class="p-3">
              @if($related->brand)
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">{{ strtoupper($related->brand->name) }}</p>
              @endif
              <p class="text-sm font-semibold text-gray-900 leading-snug mb-2">{{ $related->name }}</p>
              <div class="flex items-center gap-1 mb-2">
                @for($i = 1; $i <= 5; $i++)
                  <span class="{{ $i <= round($related->product_rating) ? 'star-filled' : 'star-empty' }} text-xs">★</span>
                @endfor
                <span class="text-xs text-gray-400">({{ $related->product_reviews_count }})</span>
              </div>
              <div class="flex items-center justify-between">
                <p class="text-sm font-bold text-emerald-600">${{ number_format((float) $related->base_price, 2) }}</p>
                <a href="{{ route('v2.products.show', $related->slug) }}" class="text-xs text-emerald-600 font-medium hover:underline shrink-0">View →</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</main>

@endsection
