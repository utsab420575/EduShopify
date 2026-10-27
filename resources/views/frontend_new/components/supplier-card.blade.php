{{-- Included via @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
     — a plain partial, not an anonymous Blade component (this directory
     isn't on Blade's auto-discovered component path).
     Optional:
       'maxAchievements'    — achievement badges before "+N" (default 2)
       'verifiedWhenActive' — show "Verified" while the account is active,
                              instead of once an admin approved it (default false)
       'featured'           — show the "★ Featured" badge (has the featured_supplier benefit) --}}
<a href="{{ route('v2.suppliers.show', $supplier->slug) }}" class="group flex flex-col h-full bg-white rounded-2xl border border-gray-200 hover:border-emerald-200 hover:shadow-md transition-all duration-200 overflow-hidden">

  <div class="relative h-32 bg-emerald-50">
    {{-- Banner Image with hover zoom --}}
    <div class="absolute inset-0 overflow-hidden">
      @if($supplier->banner)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($supplier->banner) }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
      @else
        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100/50 text-emerald-600 font-bold text-2xl">
          {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
        </div>
      @endif
    </div>

    {{-- Top-Left Supplier Type Badge with Hover Tooltip --}}
    @php
      $supplierTypes = $supplier->account?->supplierTypes;
      $primaryType = $supplierTypes?->first()?->name;
      $totalTypes = $supplierTypes ? $supplierTypes->count() : 0;
      $extraTypesCount = max(0, $totalTypes - 1);
    @endphp

    @if($primaryType)
      <div class="absolute top-2.5 left-2.5 z-20 group/type">
        <span class="inline-flex items-center gap-1 bg-gray-900/85 hover:bg-gray-950 backdrop-blur-sm text-white text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 rounded shadow-sm cursor-help transition-all duration-150 border border-white/10" title="{{ $supplierTypes->pluck('name')->implode(' · ') }}">
          <span>{{ Str::limit($primaryType, 16) }}</span>
          @if($extraTypesCount > 0)
            <span class="bg-emerald-500 text-white text-[9px] font-bold px-1 rounded-full leading-none">+{{ $extraTypesCount }}</span>
          @endif
        </span>

        @if($totalTypes > 1)
          <div class="absolute left-0 top-full mt-1.5 hidden group-hover/type:flex flex-col gap-1 z-30 bg-gray-950/95 backdrop-blur-md text-white text-[11px] rounded-lg p-2.5 shadow-xl whitespace-nowrap pointer-events-none border border-white/10 min-w-[130px]">
            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-800 pb-1 mb-0.5">Supplier Types</span>
            @foreach($supplierTypes as $type)
              <span class="flex items-center gap-1.5 text-gray-200 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                {{ $type->name }}
              </span>
            @endforeach
          </div>
        @endif
      </div>
    @endif

    {{-- Favorite Heart Button --}}
    @include('frontend_new.components.supplier-save-btn', ['supplier' => $supplier, 'class' => 'absolute top-2.5 right-2.5 z-20'])

    {{-- Featured (featured_supplier subscription benefit / admin override) --}}
    @if($featured ?? false)
      <span class="absolute bottom-2 right-2.5 z-20 inline-flex items-center gap-1 bg-amber-400 text-amber-950 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full shadow-sm" title="Featured supplier">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.75 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29z"/></svg>
        Featured
      </span>
    @endif
  </div>

  <div class="p-4 flex-1 flex flex-col">
    <div class="flex items-start gap-3 -mt-8 mb-2 relative">
      <div class="w-12 h-12 rounded-xl bg-white border-2 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0">
        @if($supplier->profile_photo)
          <img src="{{ \Illuminate\Support\Facades\Storage::url($supplier->profile_photo) }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover" />
        @else
          <div class="w-full h-full bg-emerald-50 flex items-center justify-center">
            <span class="text-sm font-bold text-emerald-600">{{ strtoupper(substr($supplier->display_name, 0, 1)) }}</span>
          </div>
        @endif
      </div>
      <div class="pt-5 min-w-0 flex-1">
        <h3 class="font-bold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors truncate leading-tight">{{ $supplier->display_name }}</h3>
      </div>
    </div>

    {{-- Every row below always renders at a fixed height — empty when
         there's no data — so all cards in a grid line up at the same size.
         Each row stays on one line: the first few items, then "+N" (full
         list in the title tooltip; all of it on the profile page). --}}
    @php
      $achievements = $supplier->publicAchievements();
      $shownAchievements = $achievements->take($maxAchievements ?? 2);
      $extraAchievements = $achievements->count() - $shownAchievements->count();
      $cardVerified = ($verifiedWhenActive ?? false)
          ? $supplier->account?->status === 'active'
          : $supplier->isVerified();

      $typeNames = $supplierTypes?->pluck('name') ?? collect();
      $extraTypes = max(0, $typeNames->count() - 2);

      $countries = $supplier->locationCountries();
      $firstCountry = $countries->first();
    @endphp

    <div class="flex items-center gap-1 h-6 mb-2 overflow-hidden" title="{{ collect([$cardVerified ? 'Verified' : null])->merge($achievements->map->badgeLabel())->filter()->implode(' · ') }}">
      @if($cardVerified)
        <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded-full inline-flex items-center gap-1 shrink-0 whitespace-nowrap leading-none">✓ Verified</span>
      @endif
      @foreach($shownAchievements as $achievement)
        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full min-w-0 truncate inline-flex items-center leading-none" style="{{ $achievement->badgeStyle() }}">{{ $achievement->badgeLabel() }}</span>
      @endforeach
      @if($extraAchievements > 0)
        <span class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full shrink-0 leading-none">+{{ $extraAchievements }}</span>
      @endif
    </div>

    <p class="flex items-center gap-1 h-4 text-[11px] text-gray-500 mb-2.5 overflow-hidden" title="{{ $typeNames->implode(' · ') }}">
      <span class="truncate min-w-0">{{ $typeNames->take(2)->implode(' · ') }}</span>
      @if($extraTypes > 0)
        <span class="shrink-0 text-gray-400 font-semibold">+{{ $extraTypes }}</span>
      @endif
    </p>

    <div class="flex items-center justify-between gap-2 h-4 text-[11px] text-gray-500 mb-3">
      <span class="flex items-center gap-1 text-amber-600 font-medium shrink-0"><span class="star">★</span> {{ number_format((float) $supplier->rating, 1) }} <span class="text-gray-400 font-normal">({{ $supplier->reviews_count ?? 0 }})</span></span>
      <span class="flex items-center gap-1 min-w-0" title="{{ $countries->pluck('name')->implode(' · ') }}">
        @if($firstCountry)
          <span class="truncate">{{ $firstCountry->flag_emoji }} {{ $firstCountry->name }}</span>
          @if($countries->count() > 1)
            <span class="shrink-0 text-gray-400 font-semibold">+{{ $countries->count() - 1 }}</span>
          @endif
        @endif
      </span>
    </div>

    <div class="flex items-center justify-between mt-auto">
      <span class="text-[11px] text-gray-500">🛍 {{ $supplier->product_count }}+ Products</span>
      <span class="text-[11px] text-emerald-600 font-semibold group-hover:underline">View Profile →</span>
    </div>
  </div>
</a>
