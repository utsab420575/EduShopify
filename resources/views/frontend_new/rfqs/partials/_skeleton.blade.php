{{-- RFQ Card Skeleton Loading State (Skeleton Screen / Placeholder) --}}
<div id="rfq-skeleton-list" class="flex flex-col gap-4">
  @for($i = 0; $i < 3; $i++)
    <div class="rfq-card p-5 sm:p-6 bg-white border border-gray-200 rounded-xl overflow-hidden relative shadow-sm">
      {{-- Top row: badge + bids count placeholder --}}
      <div class="flex items-center justify-between gap-4 mb-3.5">
        <div class="skeleton-box h-5 w-20 bg-slate-200 rounded"></div>
        <div class="skeleton-box h-4 w-14 bg-slate-200 rounded"></div>
      </div>

      {{-- Title placeholder --}}
      <div class="skeleton-box h-5 w-4/5 sm:w-3/5 bg-slate-200 rounded mb-3"></div>

      {{-- Category summary placeholder --}}
      <div class="skeleton-box h-4 w-2/5 sm:w-1/4 bg-slate-100 rounded mb-4"></div>

      {{-- Meta items placeholder --}}
      <div class="flex flex-wrap items-center gap-4">
        <div class="skeleton-box h-4 w-32 bg-slate-100 rounded"></div>
        <div class="skeleton-box h-4 w-36 bg-slate-100 rounded"></div>
        <div class="skeleton-box h-4 w-24 bg-slate-100 rounded"></div>
      </div>
    </div>
  @endfor
</div>
