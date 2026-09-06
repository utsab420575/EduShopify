{{-- Header / Search Section --}}
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
