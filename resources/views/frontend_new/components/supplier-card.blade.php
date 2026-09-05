{{-- Included via @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
     — a plain partial, not an anonymous Blade component (this directory
     isn't on Blade's auto-discovered component path). --}}
<div class="supplier-card p-3">
  <div class="flex items-start justify-between mb-2">
    <div class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-sm shrink-0">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</span>
      <div>
        <p class="font-semibold text-sm text-gray-900 leading-tight">{{ $supplier->display_name }}</p>
        <p class="text-xs text-gray-500">{{ $supplier->account?->supplierTypes?->pluck('name')->implode(' · ') }}</p>
      </div>
    </div>
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
    <a href="{{ route('v2.suppliers.show', $supplier->slug) }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
  </div>
</div>
