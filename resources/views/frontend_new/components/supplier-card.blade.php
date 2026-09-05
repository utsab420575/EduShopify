{{-- Included via @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
     — a plain partial, not an anonymous Blade component (this directory
     isn't on Blade's auto-discovered component path). --}}
<a href="{{ route('v2.suppliers.show', $supplier->slug) }}" class="group block bg-white rounded-2xl border border-gray-200 hover:border-emerald-200 hover:shadow-md transition-all duration-200 overflow-hidden">

  <div class="relative h-32 bg-emerald-50 overflow-hidden">
    @if($supplier->banner)
      <img src="{{ \Illuminate\Support\Facades\Storage::url($supplier->banner) }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
    @endif
    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="absolute top-3 right-3 z-10 w-7 h-7 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors shadow-sm">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </button>
  </div>

  <div class="p-4">
    <div class="flex items-start gap-3 -mt-8 mb-2 relative">
      <div class="w-12 h-12 rounded-xl bg-white border-2 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0">
        <div class="w-full h-full bg-emerald-50 flex items-center justify-center">
          <span class="text-sm font-bold text-emerald-600">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</span>
        </div>
      </div>
      <div class="pt-5 min-w-0 flex-1">
        <h3 class="font-bold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors truncate leading-tight">{{ $supplier->display_name }}</h3>
      </div>
    </div>

    <div class="flex flex-wrap gap-1 mb-2.5">
      <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded-full inline-flex items-center gap-1">✓ Verified</span>
      @if($supplier->demo_founding)
        <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded-full">Founding</span>
      @endif
      @if($supplier->demo_ise)
        <span class="badge-ise text-[10px] font-medium px-1.5 py-0.5 rounded-full">ISE Exhibitor</span>
      @endif
    </div>

    @if($supplier->account?->supplierTypes?->isNotEmpty())
      <p class="text-[11px] text-gray-500 leading-relaxed mb-2.5 truncate">{{ $supplier->account->supplierTypes->pluck('name')->implode(' · ') }}</p>
    @endif

    <div class="flex items-center justify-between text-[11px] text-gray-500 mb-3">
      <span class="flex items-center gap-1 text-amber-600 font-medium"><span class="star">★</span> {{ number_format((float) $supplier->rating, 1) }} <span class="text-gray-400 font-normal">({{ $supplier->reviews_count ?? 0 }})</span></span>
      @if($supplier->country)
        <span class="flex items-center gap-1">{{ $supplier->country->flag_emoji }} {{ $supplier->country->name }}</span>
      @endif
    </div>

    <div class="flex items-center justify-between">
      <span class="text-[11px] text-gray-500">🛍 {{ $supplier->product_count }}+ Products</span>
      <span class="text-[11px] text-emerald-600 font-semibold group-hover:underline">View Profile →</span>
    </div>
  </div>
</a>
