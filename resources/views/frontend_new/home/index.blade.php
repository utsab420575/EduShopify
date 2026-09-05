@extends('frontend_new.layouts.app')

@section('title', 'Edushopify – Global Suppliers for Education')

@section('content')

{{-- ───────────────────────────── HERO ───────────────────────────── --}}
<section class="relative h-[420px] overflow-hidden">
  <img src="{{ asset('images/herosection.png') }}" alt="Education tech" class="absolute inset-0 w-full h-full object-cover object-center" />
  <div class="hero-overlay absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-6 h-full flex flex-col justify-center">
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight max-w-xl">
      Global Suppliers for<br />Education. All in One Place.
    </h1>
    <p class="text-white/80 mt-3 text-base font-normal max-w-sm">Connect with verified education suppliers worldwide</p>
    <div class="flex gap-3 mt-7">
      <button class="bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-lg text-sm hover:bg-gray-100 transition">Find Suppliers</button>
      <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition">Post an RFQ</button>
    </div>
  </div>
</section>

{{-- ───────────────────────── FEATURED SUPPLIERS ─────────────────────── --}}
@if($featuredSuppliers->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-bold text-lg text-gray-900">Featured Suppliers</h2>
    <div class="flex items-center gap-2">
      <a href="#" class="text-emerald-600 text-sm font-medium hover:underline">See all</a>
      <button class="border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center hover:bg-gray-100">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </button>
    </div>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($featuredSuppliers as $supplier)
      @php
        $profileUrl = \Illuminate\Support\Facades\Route::has('v2.suppliers.show') ? route('v2.suppliers.show', $supplier->slug) : '#';
        $typeLabel = $supplier->account?->supplierTypes?->pluck('name')->implode(' · ');
      @endphp
      <div class="supplier-card">
        <div class="relative">
          @if($supplier->banner)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($supplier->banner) }}" alt="{{ $supplier->display_name }}" class="w-full h-44 object-cover" />
          @else
            <div class="w-full h-44 flex items-center justify-center bg-gray-100 text-gray-400 text-3xl font-bold">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</div>
          @endif
          @if($typeLabel)
            <span class="absolute top-2 left-2 badge-manufacturer text-gray-600 text-[10px] font-semibold px-2 py-0.5 rounded-sm bg-gray-800 text-white uppercase tracking-wide">{{ Str::limit($typeLabel, 20) }}</span>
          @endif
          <button class="absolute top-2 right-2 bg-white rounded-full w-7 h-7 flex items-center justify-center shadow">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </button>
        </div>
        <div class="p-3">
          <div class="flex items-center gap-1 mb-1">
            <span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-xs">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</span>
            <p class="font-semibold text-sm text-gray-900">{{ $supplier->display_name }}</p>
          </div>
          <div class="flex flex-wrap gap-1 mb-2">
            <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded-full flex items-center gap-1">✓ Verified</span>
            @if($supplier->demo_founding)
              <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded-full">Founding Supplier</span>
            @endif
            @if($supplier->demo_ise)
              <span class="badge-ise text-[10px] font-medium px-1.5 py-0.5 rounded-full">ISE Exhibitor</span>
            @endif
          </div>
          @if($typeLabel)
            <p class="text-xs text-gray-500 mb-1.5">{{ $typeLabel }}</p>
          @endif
          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>{{ $supplier->country?->flag_emoji }} {{ $supplier->country?->name }}</span>
          </div>
          <div class="flex items-center justify-between mt-1">
            <span class="text-xs text-gray-600"><span class="star">★</span> {{ number_format((float) $supplier->rating, 1) }} <span class="text-gray-400">({{ $supplier->reviews_count ?? 0 }})</span></span>
            <a href="{{ $profileUrl }}" class="text-emerald-600 text-xs font-medium hover:underline">View Profile →</a>
          </div>
          <p class="text-xs text-gray-400 mt-1">🛍 {{ $supplier->product_count }}+ Products</p>
        </div>
      </div>
    @endforeach
  </div>
</section>
@endif

{{-- ───────────────────────────── ALL SUPPLIERS ───────────────────────────── --}}
@if($allSuppliers->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 pb-12">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg text-gray-900">All Suppliers</h2>
    <a href="#" class="text-emerald-600 text-sm font-medium hover:underline">View all →</a>
  </div>

  <div class="flex items-center gap-2 flex-wrap mb-5">
    <button class="tab-btn tag-active text-xs font-medium px-3 py-1.5 rounded-full" data-cat="all">All Categories</button>
    @foreach($allSuppliersTabs as $tab)
      <button class="tab-btn tag-inactive text-xs font-medium px-3 py-1.5 rounded-full hover:bg-gray-200" data-cat="{{ $tab->slug }}">{{ $tab->name }}</button>
    @endforeach
    <button class="filter-btn ml-auto">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4"/></svg>
      Filter
    </button>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach($allSuppliers as $supplier)
      @php
        $profileUrl = \Illuminate\Support\Facades\Route::has('v2.suppliers.show') ? route('v2.suppliers.show', $supplier->slug) : '#';
      @endphp
      <div class="supplier-item" data-cat="{{ $supplier->home_category?->slug }}">
        <div class="supplier-card p-3">
          <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-2">
              <span class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-sm shrink-0">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</span>
              <div>
                <p class="font-semibold text-sm text-gray-900 leading-tight">{{ $supplier->display_name }}</p>
                <p class="text-xs text-gray-500">{{ $supplier->account?->supplierTypes?->pluck('name')->implode(' · ') }}</p>
              </div>
            </div>
            <button><svg class="w-4 h-4 text-gray-300 hover:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
          </div>
          <div class="flex gap-1 mb-2 flex-wrap">
            <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded-full inline-flex items-center gap-1">✓ Verified</span>
            @if($supplier->demo_founding)
              <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded-full">Founding</span>
            @endif
          </div>
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span><span class="star">★</span> {{ number_format((float) $supplier->rating, 1) }} <span class="text-gray-400">({{ $supplier->reviews_count ?? 0 }})</span></span>
            <span>{{ $supplier->country?->flag_emoji }} {{ $supplier->country?->name }}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-gray-400">🛍 {{ $supplier->product_count }}+ Products</span>
            <a href="{{ $profileUrl }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</section>
@endif

{{-- ───────────────────────── WHY CHOOSE ───────────────────────── --}}
<section class="bg-gray-50 py-14 px-4">
  <div class="max-w-3xl mx-auto text-center mb-10">
    <p class="text-emerald-600 text-xs font-semibold uppercase tracking-widest mb-2">Our Advantage</p>
    <h2 class="text-3xl font-bold text-gray-900">Why Choose <span class="text-emerald-500">Edushopify</span></h2>
    <p class="text-gray-500 mt-3 text-sm leading-relaxed max-w-sm mx-auto">The trusted platform for education procurement — connecting buyers with verified suppliers worldwide.</p>
  </div>

  <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    <div class="flex flex-col items-center gap-3">
      <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center">
        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
      </div>
      <div>
        <p class="font-semibold text-sm text-gray-900">Verified Suppliers</p>
        <p class="text-xs text-gray-500 mt-0.5">Trusted &amp; Verified</p>
      </div>
    </div>
    <div class="flex flex-col items-center gap-3">
      <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center">
        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      </div>
      <div>
        <p class="font-semibold text-sm text-gray-900">Global Reach</p>
        <p class="text-xs text-gray-500 mt-0.5">Suppliers in 50+ Countries</p>
      </div>
    </div>
    <div class="flex flex-col items-center gap-3">
      <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center">
        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <div>
        <p class="font-semibold text-sm text-gray-900">RFQ Opportunities</p>
        <p class="text-xs text-gray-500 mt-0.5">Get Relevant Business</p>
      </div>
    </div>
    <div class="flex flex-col items-center gap-3">
      <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center">
        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <p class="font-semibold text-sm text-gray-900">Secure &amp; Reliable</p>
        <p class="text-xs text-gray-500 mt-0.5">Safe Communication</p>
      </div>
    </div>
  </div>
</section>

{{-- ───────────────────────── EVENTS ───────────────────────── --}}
@if($events->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-2">
    <p class="text-emerald-600 text-xs font-semibold uppercase tracking-widest">Don't Miss Out</p>
    <h2 class="text-3xl font-bold text-gray-900 mt-2">Education <span class="text-emerald-500">STEM &amp; Robotics</span> Events</h2>
    <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto leading-relaxed">Discover the world's top education and technology exhibitions — all in one place.</p>
  </div>

  <div class="flex justify-end mb-4">
    <a href="#" class="text-emerald-600 text-sm font-medium hover:underline">View all →</a>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($events as $event)
      <div class="event-card group cursor-pointer">
        <div class="relative h-40 overflow-hidden rounded-lg">
          <img src="{{ asset('images/herosection.png') }}" alt="{{ $event['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
          <div class="absolute inset-0 bg-gradient-to-t {{ $event['overlay_class'] }}"></div>
          <span class="absolute top-2 left-2 event-badge {{ $event['badge_class'] }} text-white">{{ $event['category'] }}</span>
          <div class="absolute bottom-3 left-3 right-3">
            <p class="text-white font-bold text-sm leading-tight">{{ $event['title'] }}</p>
          </div>
        </div>
        <div class="pt-2 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">{{ $event['date'] }}</p>
            <p class="text-xs text-gray-400">📍 {{ $event['location'] }}</p>
          </div>
          <span class="text-gray-400 hover:text-gray-700">→</span>
        </div>
      </div>
    @endforeach
  </div>
</section>
@endif

@endsection
