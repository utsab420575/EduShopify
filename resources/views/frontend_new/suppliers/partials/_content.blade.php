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

<main id="supplier-content" data-active-category="{{ $activeCategory }}" class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  {{-- ── Category Tabs ── --}}
  <div class="flex items-center gap-2 flex-wrap mb-6" id="supplier-tabs-row">
    <a
      href="{{ route('v2.suppliers.index', array_filter(['search' => $search])) }}"
      data-category="all"
      class="category-tab-btn {{ $activeCategory === 'all' ? 'active' : '' }}"
    >
      All Categories
    </a>
    @foreach($categories as $cat)
      @if($cat->supplier_count > 0)
        <a
          href="{{ route('v2.suppliers.index', array_filter(['category' => $cat->slug, 'search' => $search])) }}"
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
    {{ Str::plural('supplier', $totalSuppliers) }} found
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
          @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
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
