@extends('frontend_new.layouts.app')

@section('title', $supplier->display_name . ' – Supplier Profile – Edushopify')

@section('content')
<div class="supplier-profile-page"
     @auth
     x-data="supplierContactChat({ recipientAccountId: {{ (int) $supplier->account_id }}, recipientName: {{ Illuminate\Support\Js::from($supplier->display_name) }} })"
     @endauth>
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

  {{-- Hero + Stats --}}
  <div class="hero-wrap">
    <div class="hero-banner">
      @if($supplier->banner)
        {{-- banner_fit (supplier's choice): cover crops overflow, contain shows the
             whole image over a blurred copy of itself, fill stretches to the box. --}}
        @php($bannerFit = in_array($supplier->banner_fit, \App\Models\SupplierProfile::BANNER_FITS, true) ? $supplier->banner_fit : 'cover')
        @php($bannerUrl = \Illuminate\Support\Facades\Storage::url($supplier->banner))
        @if($bannerFit === 'contain')
          <img src="{{ $bannerUrl }}" alt="" aria-hidden="true"
               style="position:absolute;inset:0;object-fit:cover;filter:blur(24px) brightness(.75);transform:scale(1.15);" />
        @endif
        <img src="{{ $bannerUrl }}" alt="{{ $supplier->display_name }}" style="position:relative;object-fit:{{ $bannerFit }};" />
      @else
        <div class="w-full h-full bg-gradient-to-r from-emerald-800 via-teal-700 to-slate-900 relative">
          <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] opacity-15"></div>
        </div>
      @endif
      @include('frontend_new.components.supplier-save-btn', ['supplier' => $supplier, 'class' => 'absolute top-3.5 right-3.5 z-20'])
      <div class="profile-bar">
        <div class="flex items-end gap-3">
          {{-- supplier_profiles.profile_photo in the avatar box; first letter when there's none --}}
          <div class="supplier-avatar" @if($supplier->profile_photo) style="overflow:hidden;" @endif>
            @if($supplier->profile_photo)
              <img src="{{ \Illuminate\Support\Facades\Storage::url($supplier->profile_photo) }}" alt="{{ $supplier->display_name }}"
                   style="width:100%;height:100%;object-fit:cover;display:block;" />
            @else
              {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
            @endif
          </div>
          <div>
            <h1 class="text-white font-bold text-2xl leading-tight mb-1.5">{{ $supplier->display_name }}</h1>
            {{-- Same Verified flag, achievements and location countries as
                 the supplier card (SupplierProfile helpers) — all of them
                 here, where the card shows the first few plus "+N". --}}
            @php($heroCountries = $supplier->locationCountries())
            <div class="flex flex-wrap items-center gap-2 mb-2">
              @if($supplier->isVerified())
                <span class="badge-verified"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Verified Supplier</span>
              @endif
              @foreach($supplier->publicAchievements() as $achievement)
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="{{ $achievement->badgeStyle() }}">{{ $achievement->badgeLabel() }}</span>
              @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-4 text-[12px] text-white/85">
              @if($heroCountries->isNotEmpty())
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $heroCountries->map(fn ($c) => trim($c->flag_emoji.' '.$c->name))->implode(' · ') }}</span>
              @endif
              @if($supplier->account?->supplierTypes?->isNotEmpty())
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5"/></svg>{{ $supplier->account->supplierTypes->pluck('name')->implode(' · ') }}</span>
              @endif
              @if($supplier->founded_year)
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Since {{ $supplier->founded_year }}</span>
              @endif
              <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>{{ $productCount }} Products</span>
              <span class="flex items-center gap-1.5"><span class="star-filled">★</span>{{ number_format((float) $supplier->rating, 1) }} <span class="text-white/70">({{ $supplier->reviews_count ?? 0 }} Reviews)</span></span>
            </div>
          </div>
        </div>
        <div class="hidden sm:flex flex-col gap-2 shrink-0">
          <a href="{{ auth()->check() ? route('buyer.rfqs.create', ['supplier' => $supplier->account_id]) : route('v2.handoff.request-quote-supplier', $supplier->slug) }}" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-5 py-2.5 rounded-md flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Request Quotation
          </a>
          @auth
            <button type="button" @click="openChat()" class="bg-white/90 hover:bg-white text-gray-800 text-sm font-medium px-5 py-2.5 rounded-md flex items-center gap-2 border border-white/40 transition-colors cursor-pointer">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
            </button>
          @else
            <a href="{{ route('v2.handoff.contact-supplier', $supplier->slug) }}" class="bg-white/90 hover:bg-white text-gray-800 text-sm font-medium px-5 py-2.5 rounded-md flex items-center gap-2 border border-white/40 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
            </a>
          @endauth
        </div>
      </div>
    </div>
    <div class="stats-bar">
      <div class="grid grid-cols-5 divide-x divide-gray-100">
        <div class="stat-col"><p class="val">{{ $supplier->quotation_response_rate !== null ? round($supplier->quotation_response_rate).'%' : '—' }}</p><p class="lbl">Response Rate</p></div>
        <div class="stat-col"><p class="val">{{ $avgResponseHours !== null ? $avgResponseHours.' hrs' : '—' }}</p><p class="lbl">Avg Response Time</p></div>
        <div class="stat-col"><p class="val">{{ $rfqsCompleted }}+</p><p class="lbl">RFQs Completed</p></div>
        <div class="stat-col"><p class="val">{{ $countriesServedDisplay ?? ($countriesServed.'+') }}</p><p class="lbl">{{ ($isWorldwide ?? false) ? 'Global Coverage' : 'Countries Served' }}</p></div>
        <div class="stat-col"><p class="val">{{ $yearsInBusiness ?? '—' }}{{ $yearsInBusiness ? '+' : '' }}</p><p class="lbl">Years in Business</p></div>
      </div>
    </div>
  </div>

  {{-- Tab Bar --}}
  <div class="tab-bar mb-5">
    <button class="tab-btn active" onclick="switchTab(this,'about')">About Us</button>
    <button class="tab-btn" onclick="switchTab(this,'products')">Products</button>
    <button class="tab-btn" onclick="switchTab(this,'services')">Services</button>
    <button class="tab-btn" data-tab="gallery-videos" id="tab-btn-gallery-videos" onclick="switchTab(this,'gallery-videos')">Gallery &amp; Video</button>
    <button class="tab-btn" onclick="switchTab(this,'certifications')">Certifications</button>
    <button class="tab-btn" onclick="switchTab(this,'reviews')">Reviews</button>
    <button class="tab-btn" onclick="switchTab(this,'contact')">Contact</button>
  </div>

  <div class="flex flex-col lg:flex-row gap-5 items-start">

    {{-- ═══ LEFT ═══ --}}
    <div class="w-full lg:flex-1 lg:max-w-[calc(100%-335px)] min-w-0 flex flex-col gap-4">

      {{-- ABOUT TAB --}}
      <div id="tab-about" class="tab-panel flex flex-col gap-4" style="display:flex;flex-direction:column;gap:16px;">

        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-3">About Us</h2>
          <div class="flex gap-4">
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-600 leading-relaxed about-clamp">
                <span class="text-emerald-600 font-medium">{{ $supplier->display_name }}</span>
                {{ $supplier->description ?? 'has not added a company description yet.' }}
              </p>
              @if($supplier->description && strlen($supplier->description) > 180)
                <button type="button" class="about-readmore-btn" onclick="toggleAboutReadMore(this)">Read more</button>
              @endif
            </div>
            @php($activeVideos = $supplier->videos->where('is_active', true))
            @if($activeVideos->isNotEmpty())
              @php($firstVideo = $activeVideos->first())
              <div class="shrink-0 w-36 h-24 relative rounded-lg overflow-hidden cursor-pointer group" onclick="switchTab(document.getElementById('tab-btn-gallery-videos') || document.querySelector('[data-tab=gallery-videos]'),'gallery-videos')">
                @if($firstVideo->thumbnailUrl())
                  <img src="{{ $firstVideo->thumbnailUrl() }}" alt="{{ $firstVideo->title }}" class="absolute inset-0 w-full h-full object-cover" />
                @else
                  <div class="absolute inset-0 bg-slate-900 flex items-center justify-center">
                    @if($firstVideo->isDirectVideo() || !in_array($firstVideo->resolvedProvider(), ['youtube', 'vimeo']))
                      <video src="{{ $firstVideo->video_url }}" class="w-full h-full object-cover opacity-60" preload="metadata" muted></video>
                    @else
                      <div class="w-full h-full bg-emerald-950 flex items-center justify-center">
                        <i class="fa-solid fa-film text-xl text-emerald-400/80"></i>
                      </div>
                    @endif
                  </div>
                @endif
                <div class="absolute inset-0 bg-black/30 flex items-end justify-center pb-2">
                  <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-9 h-9 bg-white/90 rounded-full flex items-center justify-center group-hover:bg-white transition-colors shadow">
                      <svg class="w-4 h-4 text-emerald-600 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                  </div>
                  <span class="text-white text-[10px] font-medium relative z-10 bg-black/40 px-1.5 py-0.5 rounded">{{ $firstVideo->title ?? 'Company Video' }}</span>
                </div>
              </div>
            @endif
          </div>
        </div>

        @if($featuredProducts->isNotEmpty())
          <div class="sec-card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-[15px] font-bold text-gray-900">Featured Products</h2>
              <a href="#" onclick="switchTab(document.querySelectorAll('.tab-btn')[1],'products');return false;" class="text-sm text-emerald-600 font-medium hover:underline">View all products →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              @foreach($featuredProducts as $product)
                <div class="fp-card">
                  @if($product->primaryImage)
                    <img src="{{ $product->primaryImage->getUrl() }}" class="w-full h-24 object-cover" />
                  @else
                    <div class="w-full h-24 bg-gray-100"></div>
                  @endif
                  <div class="p-2.5">
                    <p class="text-sm font-semibold text-gray-900 leading-snug mb-0.5">{{ $product->name }}</p>
                    @if($product->brand)<p class="text-[11px] text-emerald-600 font-medium mb-1.5">{{ $product->brand->name }}</p>@endif
                    <a href="{{ route('v2.products.show', $product->slug) }}" class="text-[11px] text-emerald-600 font-medium hover:underline">View Details →</a>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        @if($services->isNotEmpty())
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Our Services</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              @foreach($services->take(4) as $service)
                <div class="border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow">
                  <div class="svc-icon mb-2">
                    @if($service->icon)
                      {!! $service->icon->render('text-emerald-600 text-lg flex items-center justify-center') !!}
                    @else
                      <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                    @endif
                  </div>
                  <p class="text-sm font-semibold text-gray-900 mb-1">{{ $service->title }}</p>
                  <p class="text-[11px] text-gray-500 leading-snug">{{ Str::limit($service->description, 45) }}</p>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        @if($achievements->isNotEmpty())
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Certifications &amp; Partners</h2>
            <div class="flex flex-wrap gap-2">
              @foreach($achievements->take(6) as $achievement)
                <span class="cert-pill">{{ $achievement->name }}</span>
              @endforeach
            </div>
          </div>
        @endif

      </div>{{-- /tab-about --}}

      {{-- PRODUCTS TAB --}}
      <div id="tab-products" class="tab-panel">
        <div class="sec-card">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-[15px] font-bold text-gray-900">Products ({{ $productCount }}+)</h2>
            <a href="{{ route('v2.home') }}" class="text-sm text-emerald-600 font-medium hover:underline">View in Marketplace →</a>
          </div>
          @if($allProducts->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              @foreach($allProducts as $product)
                <div class="pt-card">
                  @if($product->primaryImage)
                    <img src="{{ $product->primaryImage->getUrl() }}" class="w-full h-40 object-cover" />
                  @else
                    <div class="w-full h-40 bg-gray-100"></div>
                  @endif
                  <div class="p-3.5">
                    @if($product->brand)<p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wide mb-1">{{ strtoupper($product->brand->name) }}</p>@endif
                    <p class="text-[15px] font-bold text-gray-900 mb-2">{{ $product->name }}</p>
                    <div class="flex items-center gap-1 mb-1.5 text-sm">
                      @for($i = 1; $i <= 5; $i++)<span class="{{ $i <= round($product->product_rating) ? 'star-filled' : 'star-empty' }}">★</span>@endfor
                      <span class="text-xs text-gray-400">({{ $product->product_reviews_count }})</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                      <span class="text-xs text-gray-400 italic">${{ number_format((float) $product->base_price, 2) }}</span>
                      <a href="{{ route('v2.products.show', $product->slug) }}" class="text-sm text-emerald-600 font-semibold hover:underline flex items-center gap-1">View <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></a>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400">No published products yet.</p>
          @endif
        </div>
      </div>

      {{-- SERVICES TAB — App\Models\Service (real "services" table, status=active) --}}
      <div id="tab-services" class="tab-panel">
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Our Services</h2>
          <p class="text-sm text-gray-500 mb-5">We offer comprehensive support to ensure your institution gets the most from our solutions.</p>
          @if($services->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              @foreach($services as $service)
                <div class="border border-gray-200 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <div class="svc-icon shrink-0">
                      @if($service->icon)
                        {!! $service->icon->render('text-emerald-600 text-lg flex items-center justify-center') !!}
                      @else
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                      @endif
                    </div>
                    <div><p class="text-sm font-bold text-gray-900 mb-1">{{ $service->title }}</p><p class="text-xs text-gray-500 leading-relaxed">{{ $service->description }}</p></div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400">No services published yet.</p>
          @endif
        </div>
      </div>

      {{-- GALLERY & VIDEO TAB --}}
      <div id="tab-gallery-videos" class="tab-panel flex flex-col gap-5" style="display:none;flex-direction:column;gap:20px;">

        {{-- ── 1. GALLERY IMAGES (CAROUSEL / SLIDER) ── --}}
        <div class="sec-card">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-[15px] font-bold text-gray-900 mb-0.5">Gallery Images</h2>
              <p class="text-xs text-gray-500">Explore facilities, showroom, production process, and operations.</p>
            </div>
            @php($activeGallery = $supplier->gallery->where('is_active', true))
            @if($activeGallery->isNotEmpty())
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span id="gallery-counter">1</span> / {{ $activeGallery->count() }} photos
              </span>
            @endif
          </div>

          @if($activeGallery->isNotEmpty())
            {{-- Professional Image Carousel / Slider --}}
            <div class="gallery-slider-wrapper relative select-none" id="supplier-gallery-carousel">
              {{-- Main Slide Stage --}}
              <div class="relative w-full rounded-2xl overflow-hidden bg-gray-950 border border-gray-200 shadow-sm aspect-16/10 sm:aspect-16/9 max-h-[480px] flex items-center justify-center group">

                {{-- Floating Alt Text / Tooltip Badge (Top-Left) --}}
                @php($firstImg = $activeGallery->first())
                <div id="gallery-alt-badge" class="absolute top-3 left-3 z-20 transition-all duration-300 {{ empty($firstImg->alt_text) ? 'opacity-0 pointer-events-none' : 'opacity-100' }}">
                  <div class="relative group/alt flex items-center">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-black/60 hover:bg-black/85 text-white backdrop-blur-md border border-white/15 text-xs shadow-md transition cursor-help">
                      <i class="fa-solid fa-circle-info text-emerald-400 text-xs shrink-0"></i>
                      <span id="gallery-alt-badge-text" class="font-normal text-gray-200 truncate max-w-[200px] sm:max-w-xs md:max-w-sm">
                        {{ $firstImg->alt_text ?? '' }}
                      </span>
                    </div>
                    {{-- Expanded Hover Tooltip for longer descriptions --}}
                    <div class="absolute left-0 top-full mt-1.5 w-64 sm:w-80 p-2.5 bg-gray-950/95 text-white text-xs rounded-xl shadow-2xl backdrop-blur-md border border-white/15 opacity-0 invisible group-hover/alt:opacity-100 group-hover/alt:visible transition-all duration-200 z-30 pointer-events-none">
                      <p class="font-semibold text-[10px] text-emerald-400 uppercase tracking-wider mb-0.5">Image Description</p>
                      <p id="gallery-alt-tooltip-text" class="text-gray-200 text-xs leading-relaxed">
                        {{ $firstImg->alt_text ?? '' }}
                      </p>
                    </div>
                  </div>
                </div>

                {{-- Slide Items --}}
                <div class="gallery-track relative w-full h-full overflow-hidden">
                  @foreach($activeGallery as $index => $img)
                    <div class="gallery-slide absolute inset-0 w-full h-full transition-all duration-300 ease-out flex items-center justify-center {{ $index === 0 ? 'opacity-100 z-10 scale-100' : 'opacity-0 z-0 pointer-events-none scale-98' }}"
                         data-index="{{ $index }}"
                         data-caption="{{ $img->caption ?? '' }}"
                         data-alt="{{ $img->alt_text ?? '' }}"
                         data-src="{{ $img->image_url }}"
                         title="{{ $img->alt_text ?: ($img->caption ?: '') }}">
                      <img src="{{ $img->image_url }}"
                           alt="{{ $img->alt_text ?? ($img->caption ?? 'Gallery photo '.($index+1)) }}"
                           title="{{ $img->alt_text ?: ($img->caption ?: '') }}"
                           class="w-full h-full object-contain select-none"
                           loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                    </div>
                  @endforeach
                </div>

                {{-- Floating Left & Right Navigation Arrows --}}
                @if($activeGallery->count() > 1)
                  <button type="button"
                          id="gallery-prev-btn"
                          aria-label="Previous slide"
                          class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all hover:scale-105 active:scale-95 cursor-pointer opacity-90 hover:opacity-100">
                    <svg class="w-5 h-5 -ml-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                  </button>
                  <button type="button"
                          id="gallery-next-btn"
                          aria-label="Next slide"
                          class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all hover:scale-105 active:scale-95 cursor-pointer opacity-90 hover:opacity-100">
                    <svg class="w-5 h-5 -mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                  </button>
                @endif

                {{-- Floating Fullscreen Lightbox Button --}}
                <button type="button"
                        id="gallery-expand-btn"
                        aria-label="Expand image"
                        class="absolute top-3 right-3 z-20 w-9 h-9 rounded-xl bg-black/50 hover:bg-black/75 text-white/90 hover:text-white backdrop-blur-xs flex items-center justify-center transition cursor-pointer"
                        title="View fullscreen">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>

                {{-- Bottom Caption Overlay --}}
                <div id="gallery-caption-bar" class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-4 pt-10 text-white z-10 pointer-events-none transition-opacity duration-300 {{ empty($firstImg->caption) && empty($firstImg->alt_text) ? 'opacity-0' : 'opacity-100' }}">
                  <p id="gallery-caption-text" class="text-xs sm:text-sm font-semibold tracking-wide drop-shadow-sm text-center line-clamp-2 {{ empty($firstImg->caption) ? 'hidden' : '' }}">
                    {{ $firstImg->caption ?? '' }}
                  </p>
                  <p id="gallery-caption-alt" class="text-center line-clamp-2 drop-shadow-sm {{ empty($firstImg->caption) ? 'text-xs sm:text-sm text-white font-medium' : 'text-[11px] text-gray-300 font-normal mt-0.5' }} {{ empty($firstImg->alt_text) ? 'hidden' : '' }}">
                    <i class="fa-solid fa-tag text-[10px] mr-1 text-emerald-400"></i><span id="gallery-caption-alt-text">{{ $firstImg->alt_text ?? '' }}</span>
                  </p>
                </div>
              </div>

              {{-- Thumbnail Navigation Strip --}}
              @if($activeGallery->count() > 1)
                <div class="gallery-thumbs-strip mt-3 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                  @foreach($activeGallery as $index => $img)
                    <button type="button"
                            class="gallery-thumb-btn shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 transition-all cursor-pointer {{ $index === 0 ? 'border-emerald-600 ring-2 ring-emerald-500/20 scale-102' : 'border-gray-200 opacity-60 hover:opacity-100' }}"
                            data-index="{{ $index }}"
                            title="{{ $img->alt_text ?: ($img->caption ?: 'Photo '.($index + 1)) }}"
                            aria-label="Go to slide {{ $index + 1 }}">
                      <img src="{{ $img->image_url }}" alt="{{ $img->alt_text ?? '' }}" class="w-full h-full object-cover pointer-events-none">
                    </button>
                  @endforeach
                </div>
              @endif
            </div>
          @else
            <div class="py-8 text-center text-gray-400 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
              <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <p class="text-sm font-medium text-gray-600">No gallery photos published yet.</p>
              <p class="text-xs text-gray-400 mt-0.5">Photos uploaded by this supplier will appear here in an interactive carousel.</p>
            </div>
          @endif
        </div>

        {{-- ── 2. VIDEO SECTION ── --}}
        <div class="sec-card">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-[15px] font-bold text-gray-900 mb-0.5">Company &amp; Product Videos</h2>
              <p class="text-xs text-gray-500">Watch product demonstrations, factory overviews, and classroom showcase videos.</p>
            </div>
            @php($activeVideos = $supplier->videos->where('is_active', true))
            @if($activeVideos->isNotEmpty())
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                {{ $activeVideos->count() }} video{{ $activeVideos->count() === 1 ? '' : 's' }}
              </span>
            @endif
          </div>

          @if($activeVideos->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              @foreach($activeVideos as $video)
                @php($badge = $video->providerBadge())
                <div class="vid-card bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-xs hover:border-gray-300 transition-all">
                  <div class="relative bg-black" style="padding-bottom:56.25%; height:0; overflow:hidden;">
                    @if(in_array($video->resolvedProvider(), ['youtube', 'vimeo']) && $video->embedUrl())
                      <iframe src="{{ $video->embedUrl() }}" class="absolute top-0 left-0 w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" title="{{ $video->title }}"></iframe>
                    @else
                      <video src="{{ $video->video_url }}" poster="{{ $video->thumbnailUrl() }}" class="absolute top-0 left-0 w-full h-full object-cover" controls playsinline preload="metadata">
                        <source src="{{ $video->video_url }}" type="video/mp4">
                        <source src="{{ $video->video_url }}" type="video/webm">
                        <p class="text-xs text-white p-4">Your browser does not support HTML5 video playback. <a href="{{ $video->video_url }}" target="_blank" class="text-emerald-400 underline">Click to watch video</a>.</p>
                      </video>
                    @endif
                  </div>
                  <div class="p-3.5">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $badge['bg_class'] }}">
                        <i class="{{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                      </span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 line-clamp-1">{{ $video->title }}</h3>
                    @if($video->caption)
                      <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ $video->caption }}</p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="py-8 text-center text-gray-400 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
              <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              <p class="text-sm font-medium text-gray-600">No videos published yet.</p>
              <p class="text-xs text-gray-400 mt-0.5">Videos added by this supplier will be playable directly here.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- CERTIFICATIONS TAB — App\Models\Certification (real "certifications" table, status=approved) --}}
      <div id="tab-certifications" class="tab-panel" style="display:none;flex-direction:column;gap:16px;">
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Certifications &amp; Accreditations</h2>
          <p class="text-sm text-emerald-600 mb-5">Our products and operations are certified by leading global bodies in education and quality management.</p>
          @if($certifications->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              @foreach($certifications as $cert)
                <div class="cert-card">
                  <div class="cert-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
                  <div>
                    <p class="text-sm font-bold text-gray-900 mb-0.5">{{ $cert->certification_name }}</p>
                    <p class="text-[11px] text-emerald-600 mb-1.5">{{ $cert->certification_title }}@if($cert->certification_date) · Since {{ $cert->certification_date->format('Y') }}@endif</p>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $cert->certification_description }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400">No certifications published yet.</p>
          @endif
        </div>
        @if($achievements->isNotEmpty())
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Industry Partnerships</h2>
            <div class="flex flex-wrap gap-2">
              @foreach($achievements as $achievement)<span class="cert-pill">{{ $achievement->name }}</span>@endforeach
            </div>
          </div>
        @endif
      </div>

      {{-- REVIEWS TAB --}}
      <div id="tab-reviews" class="tab-panel">
        <div class="sec-card">

          {{--
            The static reference also shows a Quality/Delivery/Communication/
            Support sub-rating breakdown. Review only stores one overall
            rating — no such sub-metrics exist — so, as on the product page,
            that breakdown is omitted rather than inventing per-dimension
            numbers for a real supplier.
          --}}
          <div class="flex flex-col sm:flex-row items-center gap-5 p-5 border border-gray-200 rounded-lg mb-6 bg-white">
            <div class="text-center shrink-0">
              <p class="text-5xl font-extrabold text-gray-900 leading-none">{{ number_format((float) $supplier->rating, 1) }}</p>
              <div class="flex gap-0.5 justify-center mt-2 text-xl">
                @for($i = 1; $i <= 5; $i++)<span class="{{ $i <= round($supplier->rating) ? 'star-filled' : 'star-empty' }}">★</span>@endfor
              </div>
              <p class="text-xs text-gray-400 mt-1">{{ $supplier->reviews_count ?? 0 }} Reviews</p>
            </div>
          </div>

          <h2 class="text-[15px] font-bold text-gray-900 mb-4">Customer Reviews</h2>

          @forelse($reviews as $review)
            <div class="rev-card mb-3">
              <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2.5">
                  <div class="rev-av bg-emerald-100 text-emerald-700">{{ strtoupper(substr($review->buyerAccount?->display_name ?? '?', 0, 1)) }}</div>
                  <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $review->buyerAccount?->display_name ?? 'Anonymous Buyer' }}</p>
                    @if($review->buyerAccount?->buyerProfile?->organization_name)
                      <p class="text-xs text-gray-400">{{ $review->buyerAccount->buyerProfile->organization_name }}</p>
                    @endif
                  </div>
                </div>
                <span class="text-xs text-gray-400">{{ $review->published_at?->format('F Y') }}</span>
              </div>
              <div class="flex gap-0.5 mb-2 text-sm">
                @for($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}">★</span>@endfor
              </div>
              <p class="text-sm text-gray-700 leading-relaxed{{ $review->reply ? ' mb-2' : '' }}">{{ $review->comment }}</p>
              @if($review->reply)
                <div class="bg-gray-50 rounded-md p-2.5 border-l-2 border-emerald-400">
                  <p class="text-xs text-emerald-600 font-semibold mb-0.5">Supplier Reply:</p>
                  <p class="text-xs text-gray-500">{{ $review->reply->reply }}</p>
                </div>
              @endif
            </div>
          @empty
            <p class="text-sm text-gray-400">No reviews yet for this supplier.</p>
          @endforelse

        </div>
      </div>

      {{-- CONTACT TAB --}}
      <div id="tab-contact" class="tab-panel" style="display:none;flex-direction:column;gap:16px;">

        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-4">Contact Details</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            @if($supplier->contact_phone)
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.27 2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.08 6.08l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Phone</p><p class="text-sm text-gray-800 font-medium">{{ $supplier->contact_phone }}</p></div></div>
            @endif
            @if($supplier->contact_email)
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Email</p><p class="text-sm text-gray-800 font-medium">{{ $supplier->contact_email }}</p></div></div>
            @endif
            @if($supplier->website)
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Website</p><p class="text-sm text-gray-800 font-medium">{{ $supplier->website }}</p></div></div>
            @endif
            @if($supplier->city || $supplier->country)
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Head Office</p><p class="text-sm text-gray-800 font-medium">{{ collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ') }}</p></div></div>
            @endif
          </div>
          @php($contactSocials = $supplier->account?->socialLinks ? $supplier->account->socialLinks->where('is_active', true) : collect())
          @if($contactSocials->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-gray-100">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2.5">Official Social Channels</p>
              <div class="flex flex-wrap items-center gap-2">
                @foreach($contactSocials as $link)
                  <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                     class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 {{ $link->platform_brand_color }} transition-colors shadow-xs"
                     title="{{ $link->platform_name }}">
                    <i class="{{ $link->platform_icon }} text-sm"></i>
                    <span>{{ $link->platform_name }}</span>
                    @if($link->handle)
                      <span class="text-[10px] text-gray-400">({{ $link->handle }})</span>
                    @endif
                  </a>
                @endforeach
              </div>
            </div>
          @endif
        </div>

      </div>{{-- /tab-contact --}}

    </div>{{-- /LEFT --}}

    {{-- ═══ RIGHT SIDEBAR ═══ --}}
    <div class="w-full lg:w-[310px] xl:w-[330px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">

      <div class="side-card">
        <a href="{{ auth()->check() ? route('buyer.rfqs.create', ['supplier' => $supplier->account_id]) : route('v2.handoff.request-quote-supplier', $supplier->slug) }}" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-md flex items-center justify-center gap-2 mb-2.5 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Request Quotation
        </a>
        @auth
          <button type="button" @click="openChat()" class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-gray-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
          </button>
        @else
          <a href="{{ route('v2.handoff.contact-supplier', $supplier->slug) }}" class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
          </a>
        @endauth
      </div>

      @php($sidebarSocials = $supplier->account?->socialLinks ? $supplier->account->socialLinks->where('is_active', true) : collect())
      @php($sidebarLocations = $supplier->account?->locations ?? collect())
      @php($whatsappDigits = preg_replace('/\D+/', '', (string) $supplier->whatsapp))
      @if($supplier->contact_email || $supplier->website || $supplier->contact_phone || $supplier->whatsapp || $sidebarLocations->isNotEmpty() || $sidebarSocials->isNotEmpty())
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-3">Contact Information</p>
          <div class="flex flex-col gap-2.5">
            @foreach($sidebarLocations as $location)
              @php($place = collect([$location->city?->name, $location->state?->name, $location->country?->name])->filter()->implode(', '))
              @php($street = collect([$location->address_line_1, $location->address_line_2])->filter()->implode(', '))
              <div class="flex items-start gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                <div class="min-w-0">
                  @if($location->label || $sidebarLocations->count() > 1)
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                      {{ $location->label ?: \Illuminate\Support\Str::headline($location->location_type ?? 'location') }}
                    </p>
                  @endif
                  @if($place)
                    <p class="break-words text-gray-700">{{ $place }}</p>
                  @endif
                  @if($street)
                    <p class="break-words text-gray-500">{{ $street }}</p>
                  @endif
                </div>
              </div>
            @endforeach
            @if($supplier->contact_phone)
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.27 2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.08 6.08l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span>{{ $supplier->contact_phone }}</span>
              </div>
            @endif
            @if($supplier->whatsapp)
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fa-brands fa-whatsapp w-4 text-center text-[#25D366] shrink-0" aria-hidden="true"></i>
                @if($whatsappDigits)
                  <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" class="hover:underline" title="Chat on WhatsApp">{{ $supplier->whatsapp }}</a>
                @else
                  <span>{{ $supplier->whatsapp }}</span>
                @endif
              </div>
            @endif
            @if($supplier->contact_email)
              <div class="flex items-center gap-2 text-sm text-gray-600 truncate">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <a href="mailto:{{ $supplier->contact_email }}" class="hover:underline truncate">{{ $supplier->contact_email }}</a>
              </div>
            @endif
            @if($supplier->website)
              <div class="flex items-center gap-2 text-sm text-emerald-600 truncate">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <a href="{{ $supplier->website }}" target="_blank" rel="noopener noreferrer" class="hover:underline truncate">{{ $supplier->website }}</a>
              </div>
            @endif
          </div>

          @if($sidebarSocials->isNotEmpty())
            <div class="mt-3.5 pt-3 border-t border-gray-100">
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Social Channels</p>
              <div class="flex flex-wrap items-center gap-2">
                @foreach($sidebarSocials as $link)
                  <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                     class="w-8 h-8 rounded-lg border border-gray-200 bg-white flex items-center justify-center text-gray-600 {{ $link->platform_brand_color }} transition-all shadow-xs"
                     title="{{ $link->platform_name }}">
                    <i class="{{ $link->platform_icon }} text-sm"></i>
                  </a>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      @endif

      @if($supplier->businessHours->isNotEmpty())
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-3">Business Hours</p>
          @foreach($supplier->businessHours->sortBy(fn ($h) => $h->day_of_week === 0 ? 7 : $h->day_of_week) as $hour)
            <div class="h-row">
              <span class="text-gray-700">{{ substr($hour->day_name, 0, 3) }}</span>
              @if($hour->is_open)
                <span class="text-gray-500">{{ \Carbon\Carbon::parse($hour->open_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($hour->close_time)->format('g:i A') }}</span>
              @else
                <span class="closed">Closed</span>
              @endif
            </div>
          @endforeach
        </div>
      @endif

      @if($supplier->city || $supplier->country)
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-2">Head Office</p>
          <p class="text-sm text-gray-600 mb-1">{{ collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ') }}</p>
        </div>
      @endif

      <div class="side-card">
        <p class="text-sm font-semibold text-gray-900 mb-3">Share Profile</p>
        <div class="flex items-center gap-2">
          <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn flex items-center justify-center text-gray-500 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/50 transition-colors" title="Share on Facebook"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn flex items-center justify-center text-gray-500 hover:text-sky-600 hover:border-sky-200 hover:bg-sky-50/50 transition-colors" title="Share on LinkedIn"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
          <a href="https://api.whatsapp.com/send?text={{ rawurlencode(($supplier->company_name ?? 'Supplier Profile') . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn flex items-center justify-center text-gray-500 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50/50 transition-colors" title="Share on WhatsApp"><i class="fa-brands fa-whatsapp text-sm"></i></a>
          <button type="button" class="share-btn flex items-center justify-center text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors cursor-pointer" title="Copy profile link" onclick="window.fnCopyTextToClipboard && window.fnCopyTextToClipboard(window.location.href).then(() => window.fnShowToast && window.fnShowToast('Supplier profile link copied!', 'success'))"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></button>
        </div>
      </div>

    </div>{{-- /RIGHT SIDEBAR --}}

  </div>{{-- /two-col --}}

  {{-- YOU MAY ALSO LIKE --}}
  @if($similarSuppliers->isNotEmpty())
    <div class="mt-12 pt-8 border-t border-gray-100">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">You May Also Like</h2>
        <a href="{{ route('v2.suppliers.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium hover:underline">See all →</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($similarSuppliers as $s)
          <div class="product-card-fade-up" style="animation-delay: {{ $loop->index * 55 }}ms">
            @include('frontend_new.components.supplier-card', ['supplier' => $s])
          </div>
        @endforeach
      </div>
    </div>
  @endif

</main>
@auth
  @include('frontend_new.components.supplier-contact-chat-modal')
@endauth
</div>

{{-- Public Lightbox Modal --}}
<div id="public-gallery-lightbox" class="fixed inset-0 z-[300] bg-black/90 backdrop-blur-sm hidden items-center justify-center p-4 select-none">
  <button type="button" id="lightbox-close-btn" class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl p-2 cursor-pointer z-20" aria-label="Close fullscreen">
    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>

  <button type="button" id="lightbox-prev-btn" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition cursor-pointer z-20" aria-label="Previous image">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
  </button>

  <button type="button" id="lightbox-next-btn" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition cursor-pointer z-20" aria-label="Next image">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </button>

  <div class="relative max-w-5xl max-h-[85vh] flex flex-col items-center justify-center">
    <img id="lightbox-img" src="" class="max-h-[80vh] w-auto max-w-full rounded-xl object-contain shadow-2xl" alt="">
    <div id="lightbox-caption-box" class="mt-3 text-center bg-black/75 backdrop-blur-md px-5 py-2.5 rounded-2xl max-w-2xl border border-white/10 hidden shadow-xl">
      <p id="lightbox-caption" class="text-white text-sm font-semibold hidden"></p>
      <p id="lightbox-alt" class="text-xs text-gray-300 mt-0.5 font-normal hidden">
        <i class="fa-solid fa-circle-info text-[10px] mr-1 text-emerald-400"></i><span id="lightbox-alt-text"></span>
      </p>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; });
    var active = document.getElementById('tab-about');
    if (active) {
      active.style.display = 'flex';
      active.style.flexDirection = 'column';
      active.style.gap = '16px';
    }

    // Initialize Gallery Slider
    initGallerySlider();
  });

  function toggleAboutReadMore(btn) {
    var p = btn.previousElementSibling;
    if (!p) return;
    var expanded = p.classList.toggle('expanded');
    btn.textContent = expanded ? 'Read less' : 'Read more';
  }

  function initGallerySlider() {
    var wrapper = document.getElementById('supplier-gallery-carousel');
    if (!wrapper) return;

    var slides = wrapper.querySelectorAll('.gallery-slide');
    var thumbs = wrapper.querySelectorAll('.gallery-thumb-btn');
    var prevBtn = document.getElementById('gallery-prev-btn');
    var nextBtn = document.getElementById('gallery-next-btn');
    var expandBtn = document.getElementById('gallery-expand-btn');
    var counterEl = document.getElementById('gallery-counter');
    var captionBar = document.getElementById('gallery-caption-bar');
    var captionText = document.getElementById('gallery-caption-text');
    var captionAlt = document.getElementById('gallery-caption-alt');
    var captionAltText = document.getElementById('gallery-caption-alt-text');
    var altBadge = document.getElementById('gallery-alt-badge');
    var altBadgeText = document.getElementById('gallery-alt-badge-text');
    var altTooltipText = document.getElementById('gallery-alt-tooltip-text');

    var lightbox = document.getElementById('public-gallery-lightbox');
    var lightboxImg = document.getElementById('lightbox-img');
    var lightboxCaptionBox = document.getElementById('lightbox-caption-box');
    var lightboxCaption = document.getElementById('lightbox-caption');
    var lightboxAlt = document.getElementById('lightbox-alt');
    var lightboxAltText = document.getElementById('lightbox-alt-text');
    var lightboxClose = document.getElementById('lightbox-close-btn');
    var lightboxPrev = document.getElementById('lightbox-prev-btn');
    var lightboxNext = document.getElementById('lightbox-next-btn');

    if (!slides.length) return;

    var currentIndex = 0;
    var total = slides.length;

    function showSlide(index) {
      if (index < 0) index = total - 1;
      if (index >= total) index = 0;
      currentIndex = index;

      slides.forEach(function (slide, idx) {
        if (idx === currentIndex) {
          slide.classList.remove('opacity-0', 'z-0', 'pointer-events-none', 'scale-98');
          slide.classList.add('opacity-100', 'z-10', 'scale-100');
        } else {
          slide.classList.remove('opacity-100', 'z-10', 'scale-100');
          slide.classList.add('opacity-0', 'z-0', 'pointer-events-none', 'scale-98');
        }
      });

      thumbs.forEach(function (thumb, idx) {
        if (idx === currentIndex) {
          thumb.classList.remove('border-gray-200', 'opacity-60');
          thumb.classList.add('border-emerald-600', 'ring-2', 'ring-emerald-500/20', 'scale-102', 'opacity-100');
          thumb.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
        } else {
          thumb.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-500/20', 'scale-102');
          thumb.classList.add('border-gray-200', 'opacity-60');
        }
      });

      if (counterEl) {
        counterEl.textContent = currentIndex + 1;
      }

      var currentSlide = slides[currentIndex];
      var caption = currentSlide ? (currentSlide.getAttribute('data-caption') || '') : '';
      var alt = currentSlide ? (currentSlide.getAttribute('data-alt') || '') : '';
      var hasCaption = Boolean(caption.trim());
      var hasAlt = Boolean(alt.trim());

      // Update Top-Left Floating Alt Badge & Tooltip
      if (altBadge && altBadgeText) {
        if (hasAlt) {
          altBadgeText.textContent = alt;
          if (altTooltipText) altTooltipText.textContent = alt;
          altBadge.classList.remove('opacity-0', 'pointer-events-none');
          altBadge.classList.add('opacity-100');
        } else {
          altBadge.classList.remove('opacity-100');
          altBadge.classList.add('opacity-0', 'pointer-events-none');
        }
      }

      // Update Bottom Caption Overlay
      if (captionBar) {
        if (captionText) {
          if (hasCaption) {
            captionText.textContent = caption;
            captionText.classList.remove('hidden');
          } else {
            captionText.textContent = '';
            captionText.classList.add('hidden');
          }
        }

        if (captionAlt && captionAltText) {
          if (hasAlt) {
            captionAltText.textContent = alt;
            captionAlt.classList.remove('hidden');
            if (!hasCaption) {
              captionAlt.className = 'text-xs sm:text-sm text-white font-medium text-center line-clamp-2 drop-shadow-sm';
            } else {
              captionAlt.className = 'text-[11px] text-gray-300 font-normal text-center line-clamp-2 drop-shadow-sm mt-0.5';
            }
          } else {
            captionAltText.textContent = '';
            captionAlt.classList.add('hidden');
          }
        }

        if (hasCaption || hasAlt) {
          captionBar.classList.remove('opacity-0');
          captionBar.classList.add('opacity-100');
        } else {
          captionBar.classList.remove('opacity-100');
          captionBar.classList.add('opacity-0');
        }
      }

      // If lightbox is open, keep in sync
      if (lightbox && !lightbox.classList.contains('hidden') && currentSlide) {
        var src = currentSlide.getAttribute('data-src');
        if (lightboxImg) lightboxImg.src = src;
        syncLightboxCaption(caption, alt);
      }
    }

    function syncLightboxCaption(caption, alt) {
      if (!lightboxCaptionBox) return;
      var hasC = Boolean(caption && caption.trim());
      var hasA = Boolean(alt && alt.trim());
      if (hasC || hasA) {
        lightboxCaptionBox.classList.remove('hidden');
        if (lightboxCaption) {
          lightboxCaption.textContent = caption;
          lightboxCaption.classList.toggle('hidden', !hasC);
        }
        if (lightboxAlt && lightboxAltText) {
          lightboxAltText.textContent = alt;
          lightboxAlt.classList.toggle('hidden', !hasA);
        }
      } else {
        lightboxCaptionBox.classList.add('hidden');
      }
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        showSlide(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        showSlide(currentIndex + 1);
      });
    }

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function (e) {
        e.preventDefault();
        var idx = parseInt(this.getAttribute('data-index'), 10);
        showSlide(idx);
      });
    });

    // Touch Swipe Support on Slider
    var touchStartX = 0;
    var touchEndX = 0;
    wrapper.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    wrapper.addEventListener('touchend', function (e) {
      touchEndX = e.changedTouches[0].screenX;
      var diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 40) {
        if (diff > 0) {
          showSlide(currentIndex + 1);
        } else {
          showSlide(currentIndex - 1);
        }
      }
    }, { passive: true });

    // Lightbox open / close
    function openLightbox() {
      if (!lightbox) return;
      var currentSlide = slides[currentIndex];
      if (!currentSlide) return;
      var src = currentSlide.getAttribute('data-src');
      var caption = currentSlide.getAttribute('data-caption') || '';
      var alt = currentSlide.getAttribute('data-alt') || '';
      if (lightboxImg) lightboxImg.src = src;
      syncLightboxCaption(caption, alt);
      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
    }

    function closeLightbox() {
      if (!lightbox) return;
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
      if (lightboxImg) lightboxImg.src = '';
    }

    if (expandBtn) {
      expandBtn.addEventListener('click', openLightbox);
    }

    if (lightboxClose) {
      lightboxClose.addEventListener('click', closeLightbox);
    }

    if (lightbox) {
      lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) {
          closeLightbox();
        }
      });
    }

    if (lightboxPrev) {
      lightboxPrev.addEventListener('click', function (e) {
        e.stopPropagation();
        showSlide(currentIndex - 1);
      });
    }

    if (lightboxNext) {
      lightboxNext.addEventListener('click', function (e) {
        e.stopPropagation();
        showSlide(currentIndex + 1);
      });
    }

    // Keyboard navigation
    window.addEventListener('keydown', function (e) {
      if (lightbox && !lightbox.classList.contains('hidden')) {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showSlide(currentIndex - 1);
        if (e.key === 'ArrowRight') showSlide(currentIndex + 1);
      }
    });
  }
</script>
@endsection
