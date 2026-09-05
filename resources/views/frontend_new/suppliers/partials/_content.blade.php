{{-- Main Content: Category Tabs + Supplier Grid
     id="supplier-content" is swapped wholesale (outerHTML) by the hero
     search's live-filter JS — this partial is also what the controller
     renders standalone for that AJAX request, so tabs/count/pagination
     always stay correct together, from one source of truth. --}}
<main id="supplier-content" data-active-category="{{ $activeCategory }}" class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  {{-- ── Category Tabs ── --}}
  <div class="flex items-center gap-2 flex-wrap mb-6">
    <a
      href="{{ route('v2.suppliers.index', array_filter(['search' => $search])) }}"
      class="{{ $activeCategory === 'all' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }} text-xs font-semibold px-4 py-2 rounded-full transition"
    >
      All Categories
    </a>
    @foreach($categories as $cat)
      @if($cat->supplier_count > 0)
        <a
          href="{{ route('v2.suppliers.index', array_filter(['category' => $cat->slug, 'search' => $search])) }}"
          class="{{ $activeCategory === $cat->slug ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }} text-xs font-semibold px-4 py-2 rounded-full transition"
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
      for "<span class="text-emerald-600">{{ $search }}</span>"
    @endif
    @if($activeCategory && $activeCategory !== 'all')
      in <span class="text-emerald-600">{{ $categories->firstWhere('slug', $activeCategory)?->name }}</span>
    @endif
  </p>

  {{-- ── Supplier Grid ── --}}
  @if($suppliers->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @foreach($suppliers as $supplier)
        @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
      @endforeach
    </div>

    @if($suppliers->hasPages())
      <div class="mt-10 flex justify-center">
        {{ $suppliers->links('pagination::simple-tailwind') }}
      </div>
    @endif

  @else
    <div class="flex flex-col items-center justify-center py-24 text-center">
      <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <p class="text-gray-400 font-medium">No suppliers found</p>
      <p class="text-sm text-gray-300 mt-1">Try a different category or search term</p>
      <a href="{{ route('v2.suppliers.index') }}" class="mt-4 text-sm text-emerald-600 font-medium hover:underline">Clear filters →</a>
    </div>
  @endif

</main>
