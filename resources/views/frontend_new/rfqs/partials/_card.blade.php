@php
  $isBidding = $rfq->quotations_count > 0;
  $location = collect([$rfq->delivery_city, $rfq->delivery_state, $rfq->delivery_country])->filter()->first();
@endphp

<div class="rfq-card rfq-card-fade-up p-5 sm:p-6" style="animation-delay: {{ $loop->index * 45 }}ms">
  <div class="flex items-start justify-between gap-4 mb-3">
    <span class="{{ $isBidding ? 'badge-bidding' : 'badge-posted' }} text-xs font-semibold px-2.5 py-0.5 rounded">{{ $isBidding ? 'bidding' : 'posted' }}</span>
    <span class="text-sm text-gray-400 shrink-0">{{ $rfq->quotations_count }} bids</span>
  </div>

  <h2 class="text-[17px] font-bold text-gray-900 mb-2 leading-snug">
    <a href="{{ \Illuminate\Support\Facades\Route::has('v2.rfqs.show') ? route('v2.rfqs.show', $rfq->rfq_number) : '#' }}" class="hover:text-emerald-600 transition-colors">{{ $rfq->title }}</a>
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
