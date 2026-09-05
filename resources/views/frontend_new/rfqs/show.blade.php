@extends('frontend_new.layouts.app')

@section('title', $opportunity->title . ' – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
    <nav class="flex items-center gap-2 text-sm text-gray-500">
      <a href="{{ route('v2.home') }}" class="hover:text-gray-900 transition-colors">Home</a>
      <span class="text-gray-300">/</span>
      <a href="{{ route('v2.rfqs.index') }}" class="hover:text-gray-900 transition-colors">RFQs</a>
      <span class="text-gray-300">/</span>
      <span class="text-gray-900 font-medium truncate max-w-xs">{{ $opportunity->title }}</span>
    </nav>
  </div>
</div>

@php
  $isBidding = $opportunity->quotations_count > 0;
  $location = collect([$opportunity->delivery_city, $opportunity->delivery_state, $opportunity->delivery_country])->filter()->first();
@endphp

{{-- Page header --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-7 pb-5">
  <div class="flex items-center justify-between mb-3">
    <span class="{{ $isBidding ? 'badge-bidding' : 'badge-posted' }} text-xs font-semibold px-2.5 py-0.5 rounded">{{ $isBidding ? 'bidding' : 'posted' }}</span>
    <span class="text-sm text-gray-400 font-medium">{{ $opportunity->quotations_count }} bids</span>
  </div>

  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <h1 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-snug max-w-2xl">{{ $opportunity->title }}</h1>
    <div class="flex items-center gap-2 shrink-0">
      <button type="button" class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 text-sm font-medium px-3 py-2 rounded-md transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Save
      </button>
      <button type="button" onclick="fnShare()" class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 text-sm font-medium px-3 py-2 rounded-md transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        Share
      </button>
      <a href="{{ route('v2.handoff.submit-quotation', $opportunity->rfq_number) }}"
        class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-md transition-colors">
        Submit a Quote
      </a>
    </div>
  </div>

  <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 text-sm text-gray-500">
    @if($location)
      <span class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        {{ $location }}
      </span>
    @endif
    @if($opportunity->quotation_deadline)
      <span class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Deadline: {{ $opportunity->quotation_deadline->format('M j, Y') }}
      </span>
    @endif
    @if($opportunity->item_count)
      <span>{{ $opportunity->item_count }} {{ Str::plural('item', $opportunity->item_count) }} requested</span>
    @endif
    @if($opportunity->published_at)
      <span class="text-gray-400">·</span>
      <span class="text-gray-400">Posted {{ $opportunity->published_at->format('M j, Y') }}</span>
    @endif
  </div>
</div>

{{-- Two-column layout --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 pb-14">
  <div class="flex flex-col lg:flex-row gap-6 items-start">

    {{-- LEFT --}}
    <div class="w-full lg:flex-1 flex flex-col gap-5">

      {{--
        The spec's Description/Specifications/Attachments/Buyer Info/Quotes
        Received cards need real RFQ detail that this app's public data
        model deliberately does not expose (rfq_public_summary's own
        migration docblock: buyer identity, description, budget and item
        specifics are "Deliberately NOT exposed" — the existing legacy
        public RFQ page is built the same limited way). Only what's
        genuinely public is shown below; full detail requires signing in
        and being authorized on the RFQ, via "Submit a Quote".
      --}}
      <div class="detail-card p-5 sm:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-3">Requirement Summary</h2>
        @if($opportunity->category_summary)
          <p class="text-sm text-gray-600 leading-relaxed mb-2"><span class="font-medium text-gray-800">Category:</span> {{ $opportunity->category_summary }}</p>
        @endif
        @if($opportunity->item_types)
          <p class="text-sm text-gray-600 leading-relaxed mb-2"><span class="font-medium text-gray-800">Item types:</span> {{ $opportunity->item_types }}</p>
        @endif
        @if($opportunity->item_count)
          <p class="text-sm text-gray-600 leading-relaxed"><span class="font-medium text-gray-800">Items requested:</span> {{ $opportunity->item_count }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-4">Full requirements, specifications, and buyer details are shared with suppliers after submitting a quote.</p>
      </div>

    </div>{{-- /LEFT --}}

    {{-- RIGHT --}}
    <div class="w-full lg:w-80 xl:w-96 shrink-0 flex flex-col gap-5 lg:sticky lg:top-20">

      <div class="detail-card p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">RFQ Details</h3>
        <p class="text-xs text-gray-400 mb-4">Reference #{{ $opportunity->rfq_number }}</p>

        <div class="divide-y divide-gray-100">
          @if($opportunity->category_summary)
            <div class="info-row">
              <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></div>
              <div><p class="text-xs text-gray-400">Category</p><p class="text-sm font-medium text-gray-900">{{ $opportunity->category_summary }}</p></div>
            </div>
          @endif
          @if($location)
            <div class="info-row">
              <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
              <div><p class="text-xs text-gray-400">Delivery Location</p><p class="text-sm font-medium text-gray-900">{{ $location }}</p></div>
            </div>
          @endif
          @if($opportunity->item_count)
            <div class="info-row">
              <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
              <div><p class="text-xs text-gray-400">Items Requested</p><p class="text-sm font-medium text-gray-900">{{ $opportunity->item_count }}</p></div>
            </div>
          @endif
          @if($opportunity->quotation_deadline)
            <div class="info-row">
              <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
              <div>
                <p class="text-xs text-gray-400">Submission Deadline</p>
                <p class="text-sm font-medium text-gray-900">{{ $opportunity->quotation_deadline->format('M j, Y') }}</p>
                <p id="deadline-countdown" data-deadline="{{ $opportunity->quotation_deadline->toIso8601String() }}" class="text-xs text-gray-400 mt-0.5"></p>
              </div>
            </div>
          @endif
          @if($opportunity->published_at)
            <div class="info-row">
              <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
              <div><p class="text-xs text-gray-400">Posted</p><p class="text-sm font-medium text-gray-900">{{ $opportunity->published_at->format('M j, Y') }}</p></div>
            </div>
          @endif
          <div class="info-row">
            <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <div><p class="text-xs text-gray-400">Quotes Received</p><p class="text-sm font-medium text-gray-900">{{ $opportunity->quotations_count }} bids</p></div>
          </div>
        </div>
      </div>

      <div class="detail-card p-5" id="submit-quote">
        <p class="text-sm font-semibold text-gray-900 mb-1">Interested in this RFQ?</p>
        <p class="text-xs text-gray-500 mb-4 leading-relaxed">Sign in as a verified supplier to view full requirements and submit a competitive quote.</p>
        <a href="{{ route('v2.handoff.submit-quotation', $opportunity->rfq_number) }}"
          class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-md mb-2">
          Submit a Quote
        </a>
        @guest
          <a href="{{ route('register') }}" class="block text-center border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md">Register as Supplier</a>
        @endguest
      </div>

      @if($similar->isNotEmpty())
        <div class="detail-card p-5">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Similar RFQs</h3>
          <div class="flex flex-col gap-0 divide-y divide-gray-100">
            @foreach($similar as $s)
              @php($sIsBidding = $s->quotations_count > 0)
              <a href="{{ route('v2.rfqs.show', $s->rfq_number) }}" class="flex items-start justify-between gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-md transition-colors group">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-800 group-hover:text-emerald-600 leading-snug line-clamp-2">{{ $s->title }}</p>
                  @if($s->quotation_deadline)
                    <p class="text-xs text-gray-400 mt-1">Deadline {{ $s->quotation_deadline->format('M j') }}</p>
                  @endif
                </div>
                <span class="{{ $sIsBidding ? 'badge-bidding' : 'badge-posted' }} text-[10px] font-semibold px-2 py-0.5 rounded shrink-0 mt-0.5">{{ $sIsBidding ? 'bidding' : 'posted' }}</span>
              </a>
            @endforeach
          </div>
          <a href="{{ route('v2.rfqs.index') }}" class="block text-center text-sm text-emerald-600 hover:text-emerald-700 font-medium mt-4">Browse all RFQs →</a>
        </div>
      @endif

    </div>{{-- /RIGHT --}}

  </div>
</div>

@endsection
