{{-- Main Content: Category Tabs + Supplier Grid
     id="supplier-content" is swapped wholesale (outerHTML) by the hero
     search's live-filter JS — this partial is also what the controller
     renders standalone for that AJAX request, so tabs/count/pagination
     always stay correct together, from one source of truth. --}}
<style>
  /* ── Tab Buttons Styling ── */
  .category-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 1rem; /* px-4 py-1.5 */
    font-size: 0.75rem; /* text-xs */
    font-weight: 600;
    line-height: 1rem;
    border-radius: 9999px;
    border-width: 1px;
    border-style: solid;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Active Tab: Dark charcoal pill with white text */
  .category-tab-btn.active {
    background-color: #0f172a; /* slate-900 */
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  /* Inactive Tab: Clean white pill with gray border */
  .category-tab-btn:not(.active) {
    background-color: #ffffff;
    color: #4b5563; /* gray-600 */
    border-color: #e5e7eb; /* gray-200 */
  }

  /* Inactive Tab Hover: Accent emerald border + text */
  .category-tab-btn:not(.active):hover {
    border-color: #10b981; /* emerald-500 */
    color: #059669; /* emerald-600 */
    background-color: #f0fdf4;
  }

  /* ── Product Card Fade-Up Reveal Animation ── */
  @keyframes productCardFadeUp {
    0% {
      opacity: 0;
      transform: translateY(18px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .product-card-fade-up {
    animation: productCardFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    will-change: transform, opacity;
  }
</style>

@php
  $featuredOnly ??= false;
  $featuredCount ??= 0;
@endphp
<main id="supplier-content" data-active-category="{{ $activeCategory }}" data-featured="{{ $featuredOnly ? '1' : '0' }}" class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  {{-- ── Category Tabs (+ Featured filter) ── --}}
  <div class="flex items-center gap-2 flex-wrap mb-6" id="supplier-tabs-row">
    {{-- ★ Featured toggle — combines with the category tab and search (live-filter JS in _hero) --}}
    <a
      href="{{ route('v2.suppliers.index', array_filter(['category' => $activeCategory !== 'all' ? $activeCategory : null, 'search' => $search, 'featured' => $featuredOnly ? null : 1])) }}"
      id="supplier-featured-toggle"
      aria-pressed="{{ $featuredOnly ? 'true' : 'false' }}"
      class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-full border transition-colors {{ $featuredOnly ? 'bg-amber-400 border-amber-400 text-amber-950' : 'bg-white border-amber-300 text-amber-700 hover:bg-amber-50' }}"
      title="{{ $featuredOnly ? 'Show all suppliers' : 'Show only featured suppliers' }}"
    >
      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.75 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29z"/></svg>
      Featured
      @if($featuredCount > 0)<span class="opacity-75">({{ $featuredCount }})</span>@endif
      @if($featuredOnly)<span aria-hidden="true">✕</span>@endif
    </a>
    <span class="w-px h-5 bg-gray-200 mx-1" aria-hidden="true"></span>

    <a
      href="{{ route('v2.suppliers.index', array_filter(['search' => $search, 'featured' => $featuredOnly ? 1 : null])) }}"
      data-category="all"
      class="category-tab-btn {{ $activeCategory === 'all' ? 'active' : '' }}"
    >
      All Categories
    </a>
    @foreach($categories as $cat)
      @if($cat->supplier_count > 0)
        <a
          href="{{ route('v2.suppliers.index', array_filter(['category' => $cat->slug, 'search' => $search, 'featured' => $featuredOnly ? 1 : null])) }}"
          data-category="{{ $cat->slug }}"
          class="category-tab-btn {{ $activeCategory === $cat->slug ? 'active' : '' }}"
        >
          {{ $cat->name }}
        </a>
      @endif
    @endforeach
  </div>

  {{-- ── Results count ── --}}
  <p class="text-sm text-gray-500 mb-5">
    <span class="font-semibold text-gray-900">{{ number_format($totalSuppliers) }}</span>
    {{ $featuredOnly ? 'featured' : '' }} {{ Str::plural('supplier', $totalSuppliers) }} found
    @if($search)
      for "<span class="text-emerald-600 font-medium">{{ $search }}</span>"
    @endif
    @if($activeCategory && $activeCategory !== 'all')
      in <span class="text-emerald-600 font-medium">{{ $categories->firstWhere('slug', $activeCategory)?->name }}</span>
    @endif
  </p>

  {{-- ── Supplier Grid ── --}}
  @if($suppliers->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="supplier-cards-grid">
      @foreach($suppliers as $supplier)
        <div class="product-card-fade-up" style="animation-delay: {{ $loop->index * 55 }}ms">
          {{-- Badge row: "Verified" (account active), 1 achievement, then "+N" --}}
          @include('frontend_new.components.supplier-card', ['supplier' => $supplier, 'maxAchievements' => 1, 'verifiedWhenActive' => true, 'featured' => (bool) $supplier->is_featured_supplier])
        </div>
      @endforeach
    </div>

    @if($suppliers->hasPages())
      <div class="mt-10 flex justify-center">
        {{ $suppliers->links('pagination::simple-tailwind') }}
      </div>
    @endif

  @else
    <div class="col-span-full flex flex-col items-center justify-center py-24 text-center product-card-fade-up">
      <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <path d="M3 9l4.5-4.5 4.5 4.5 4.5-4.5 4.5 4.5"/>
      </svg>
      <p class="text-gray-400 font-medium">No suppliers found</p>
      <p class="text-sm text-gray-300 mt-1">Try a different category or search term</p>
      <a href="{{ route('v2.suppliers.index') }}" id="btn-clear-all-filters" class="mt-4 text-sm text-emerald-600 font-medium hover:underline">Clear filters →</a>
    </div>
  @endif

</main>
