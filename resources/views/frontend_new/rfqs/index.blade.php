@extends('frontend_new.layouts.app')

@section('title', 'Open RFQs – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

  <div class="mb-7">
    <h1 class="text-3xl font-bold text-gray-900 mb-1.5">Open RFQs</h1>
    <p class="text-sm text-gray-500">Browse active requests for quotation from institutional buyers</p>
  </div>

  <div class="relative max-w-xl mb-8">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </span>
    <input type="text" id="rfq-search" value="{{ $q }}" placeholder="Search RFQs..."
      class="w-full border border-gray-200 rounded-md text-sm pl-9 pr-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
  </div>

  <div class="flex flex-col gap-4" id="rfq-list">
    @forelse($opportunities as $rfq)
      @php
        $isBidding = $rfq->quotations_count > 0;
        $location = collect([$rfq->delivery_city, $rfq->delivery_state, $rfq->delivery_country])->filter()->first();
      @endphp
      <div class="rfq-card p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4 mb-3">
          <span class="{{ $isBidding ? 'badge-bidding' : 'badge-posted' }} text-xs font-semibold px-2.5 py-0.5 rounded">{{ $isBidding ? 'bidding' : 'posted' }}</span>
          <span class="text-sm text-gray-400 shrink-0">{{ $rfq->quotations_count }} bids</span>
        </div>
        <h2 class="text-[17px] font-bold text-gray-900 mb-2 leading-snug">
          <a href="{{ \Illuminate\Support\Facades\Route::has('v2.rfqs.show') ? route('v2.rfqs.show', $rfq->rfq_number) : '#' }}" class="hover:text-emerald-600">{{ $rfq->title }}</a>
        </h2>
        @if($rfq->category_summary)
          <p class="text-sm text-gray-500 leading-relaxed mb-4">{{ $rfq->category_summary }}</p>
        @endif
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-gray-500">
          @if($location)
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ $location }}
            </span>
          @endif
          @if($rfq->quotation_deadline)
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Deadline: {{ $rfq->quotation_deadline->format('M j, Y') }}
            </span>
          @endif
          @if($rfq->item_count)
            <span>{{ $rfq->item_count }} {{ Str::plural('item', $rfq->item_count) }} requested</span>
          @endif
          @if($rfq->published_at)
            <span class="text-gray-400">·</span>
            <span class="text-gray-400">Posted {{ $rfq->published_at->format('M j, Y') }}</span>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center py-20">
        <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-1">No open RFQs right now</p>
        <p class="text-sm text-gray-500">Check back soon for new procurement opportunities.</p>
      </div>
    @endforelse
  </div>

  {{-- Empty state for the client-side live search (hidden unless a search yields no visible cards) --}}
  <div id="empty-state" class="hidden text-center py-20">
    <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
      <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </div>
    <p class="font-semibold text-gray-900 mb-1">No RFQs found</p>
    <p class="text-sm text-gray-500">Try a different search term.</p>
  </div>

  @if($opportunities->hasPages())
    <div class="flex items-center justify-between gap-4 mt-8">
      <div>
        @if($opportunities->onFirstPage())
          <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Previous</span>
        @else
          <a href="{{ $opportunities->previousPageUrl() }}" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Previous</a>
        @endif
      </div>
      <p class="text-sm text-gray-500">Page {{ $opportunities->currentPage() }} of {{ $opportunities->lastPage() }}</p>
      <div>
        @if($opportunities->hasMorePages())
          <a href="{{ $opportunities->nextPageUrl() }}" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Next</a>
        @else
          <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Next</span>
        @endif
      </div>
    </div>
  @endif

</main>
@endsection
