{{-- Main Content: Category Tabs + Product Grid --}}
<style>
  /* ── Tab Buttons Styling (matches reference site) ── */
  .category-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 1rem; /* px-4 py-1.5 */
    font-size: 0.75rem; /* text-xs */
    font-weight: 600;
    line-height: 1rem;
    border-radius: 9999px; /* rounded-full pill */
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
    background-color: #f0fdf4; /* subtle emerald tint */
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

<main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  {{-- ── Category Tabs ── --}}
  <div class="flex items-center gap-2 flex-wrap mb-6" id="category-tabs-row">
    <a
      href="{{ route('v2.categories.index') }}"
      data-category="all"
      data-name="All Categories"
      class="category-tab-btn {{ $activeCategory === 'all' ? 'active' : '' }}"
    >
      All Categories
    </a>
    @foreach($categories as $cat)
      @if($cat->listing_count > 0)
        <a
          href="{{ route('v2.categories.index', array_filter(['category' => $cat->slug, 'search' => $search])) }}"
          data-category="{{ $cat->slug }}"
          data-name="{{ $cat->name }}"
          class="category-tab-btn {{ $activeCategory === $cat->slug ? 'active' : '' }}"
        >
          {{ $cat->name }}
        </a>
      @endif
    @endforeach
  </div>

  {{-- ── Results Count ── --}}
  <p id="cat-results-count" class="text-sm text-gray-500 mb-5 transition-opacity duration-200">
    <span class="font-semibold text-gray-900" id="cat-count-num">{{ number_format($totalProducts) }}</span>
    <span id="cat-count-label">{{ Str::plural('product', $totalProducts) }} found</span>
    <span id="cat-count-search-part">
      @if($search) for "<span class="text-emerald-600 font-medium">{{ $search }}</span>"@endif
    </span>
    <span id="cat-count-cat-part">
      @if($activeCategory && $activeCategory !== 'all') in <span class="text-emerald-600 font-medium">{{ $categories->firstWhere('slug', $activeCategory)?->name }}</span>@endif
    </span>
  </p>

  {{-- ── Product Grid ── --}}
  <div id="cat-products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    @forelse($listings as $listing)
      @include('frontend_new.components.product-card', [
        'listing' => $listing,
        'animationDelay' => $loop->index * 55,
      ])
    @empty
      <div class="col-span-full flex flex-col items-center justify-center py-20 text-center product-card-fade-up">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4 text-gray-400">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
        <p class="text-base font-semibold text-gray-800">No products found</p>
        <p class="text-sm text-gray-400 mt-1">Try a different category or search term</p>
        <button type="button" id="btn-clear-all-filters" class="mt-4 inline-flex items-center gap-1 text-sm text-emerald-600 font-medium hover:text-emerald-700 hover:underline">
          Clear filters &rarr;
        </button>
      </div>
    @endforelse
  </div>

  {{-- ── Pagination ── --}}
  <div id="cat-pagination-wrap" class="mt-10 flex justify-center {{ $listings->hasPages() ? '' : 'hidden' }}">
    {{ $listings->links('pagination::simple-tailwind') }}
  </div>

</main>

@push('scripts')
<script>
(function () {
  const searchInput    = document.getElementById('cat-search-input');
  const searchClear    = document.getElementById('cat-search-clear');
  const tabsRow        = document.getElementById('category-tabs-row');
  const productsGrid   = document.getElementById('cat-products-grid');
  const countNum       = document.getElementById('cat-count-num');
  const countLabel     = document.getElementById('cat-count-label');
  const searchPart     = document.getElementById('cat-count-search-part');
  const catPart        = document.getElementById('cat-count-cat-part');
  const paginationWrap = document.getElementById('cat-pagination-wrap');

  const endpointUrl    = '{{ route('v2.categories.index') }}';

  // Current state
  let currentCategory     = '{{ $activeCategory }}' || 'all';
  let currentCategoryName = '{{ $activeCategory === "all" ? "All Categories" : ($categories->firstWhere("slug", $activeCategory)?->name ?? "") }}';
  let currentSearch       = '{{ $search }}' || '';
  let debounceTimer       = null;
  let abortCtrl           = null;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderProductCard(p, index) {
    const priceHtml = p.base_price
      ? `<p class="text-sm font-bold text-emerald-600">${escapeHtml(p.currency || 'USD')} ${Number(p.base_price).toLocaleString()}${p.unit ? ` <span class="text-xs font-normal text-gray-400">/ ${escapeHtml(p.unit)}</span>` : ''}</p>`
      : `<p class="text-xs text-gray-400 italic">Price on request</p>`;

    const imgHtml = p.image
      ? `<img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />`
      : `<div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
           <svg class="w-12 h-12 mb-1" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9l4.5-4.5 4.5 4.5 4.5-4.5 4.5 4.5"/><circle cx="8.5" cy="7.5" r="1.5" fill="currentColor"/></svg>
           <span class="text-xs">No image</span>
         </div>`;

    const verifiedHtml = p.is_verified
      ? `<span class="badge-verified text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1">
           <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i> Verified
         </span>`
      : '';

    const featuredHtml = p.is_featured
      ? `<span class="bg-amber-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1 uppercase tracking-wide">
           <i class="fa-solid fa-star text-[9px]"></i> Featured
         </span>`
      : '';

    const isSaved = Boolean(p.is_saved);
    const savesCount = Number(p.saves_count) || 0;
    const saveHtml = `
      <div class="absolute top-2.5 right-2.5 z-10">
        <button
          type="button"
          id="fn-listing-save-${Number(p.id)}"
          class="fn-listing-save-btn group/fav flex items-center gap-1.5 px-2.5 py-1 rounded-full transition-all duration-200 text-xs font-semibold backdrop-blur-sm select-none border cursor-pointer ${isSaved ? 'is-saved bg-rose-50 hover:bg-rose-100 text-rose-600 border-rose-200 shadow-xs' : (savesCount > 0 ? 'bg-white/90 hover:bg-white text-gray-700 hover:text-rose-600 border-gray-200 shadow-xs' : 'bg-white/90 hover:bg-white text-gray-400 hover:text-rose-600 border-gray-200 shadow-xs')}"
          data-listing-slug="${escapeHtml(p.slug)}"
          data-listing-id="${Number(p.id)}"
          data-saved="${isSaved ? '1' : '0'}"
          data-saves-count="${savesCount}"
          data-save-url="/v2/product/${encodeURIComponent(p.slug)}/save"
          aria-label="${isSaved ? 'Remove from favorites' : 'Save to favorites'}"
          title="${isSaved ? `Saved to your favorites (${savesCount})` : `Save product (${savesCount})`}"
          onclick="event.preventDefault(); event.stopPropagation(); window.fnToggleListingSave && window.fnToggleListingSave(this);"
        >
          <i class="fn-fav-icon fa-${isSaved ? 'solid text-rose-500' : 'regular text-gray-400 group-hover/fav:text-rose-500'} fa-heart text-xs transition-transform duration-200"></i>
          <span id="fav-count-listing-${Number(p.id)}" class="fn-fav-count text-xs font-semibold leading-none ${isSaved ? 'text-rose-600' : 'text-gray-700 group-hover/fav:text-rose-600'} ${(savesCount > 0 || isSaved) ? '' : 'hidden'}">${savesCount}</span>
        </button>
      </div>
    `;

    return `
      <a
        href="${escapeHtml(p.url)}"
        class="group block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md hover:border-emerald-200 transition-all duration-200 product-card-fade-up relative flex flex-col h-full"
        style="animation-delay: ${index * 55}ms"
      >
        <div class="relative h-48 bg-gray-100 overflow-hidden shrink-0">
          ${imgHtml}
          <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 z-10 pointer-events-none">
            ${verifiedHtml}
            ${featuredHtml}
          </div>
          ${saveHtml}
          <div class="absolute bottom-2.5 right-2.5 z-10">
            <button
              type="button"
              onclick="event.preventDefault(); event.stopPropagation(); fnToggleCompare(${Number(p.id)});"
              data-compare-id="${Number(p.id)}"
              data-style="icon"
              title="Add to compare"
              aria-label="Add to compare"
              class="fn-compare-btn w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 ease-in-out shrink-0 cursor-pointer shadow-sm bg-white/95 text-slate-500 border border-slate-200/90 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 hover:scale-110 hover:shadow-md backdrop-blur-xs"
            >
              <i class="fa-solid fa-arrow-right-arrow-left text-xs transition-transform duration-200"></i>
            </button>
          </div>
        </div>
        <div class="p-3.5 flex flex-col flex-1">
          <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mb-1 truncate">${escapeHtml(p.category || 'Other')}</p>
          <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-0.5 line-clamp-2 group-hover:text-emerald-700 transition-colors">${escapeHtml(p.name)}</h3>
          <p class="text-xs text-gray-500 mb-3 truncate">${escapeHtml(p.brand || '—')}</p>
          <div class="mt-auto pt-2 flex items-center justify-between border-t border-gray-100">
            <div>${priceHtml}</div>
            <span class="text-xs text-emerald-600 font-semibold group-hover:underline flex items-center gap-0.5">View &rarr;</span>
          </div>
        </div>
      </a>
    `;
  }

  function renderEmptyState() {
    let sub = 'Try a different category or search term';
    if (currentSearch && currentCategoryName && currentCategory !== 'all') {
      sub = `No products matching "${escapeHtml(currentSearch)}" in ${escapeHtml(currentCategoryName)}`;
    } else if (currentSearch) {
      sub = `No products matching "${escapeHtml(currentSearch)}"`;
    }

    return `
      <div class="col-span-full flex flex-col items-center justify-center py-20 text-center product-card-fade-up">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4 text-gray-400">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
        <p class="text-base font-semibold text-gray-800">No products found</p>
        <p class="text-sm text-gray-400 mt-1">${sub}</p>
        <button type="button" id="btn-clear-all-filters" class="mt-4 inline-flex items-center gap-1 text-sm text-emerald-600 font-medium hover:text-emerald-700 hover:underline">
          Clear filters &rarr;
        </button>
      </div>
    `;
  }

  function updateResultsHeader(total) {
    if (countNum)   countNum.textContent   = total.toLocaleString();
    if (countLabel) countLabel.textContent = total === 1 ? 'product' : 'products';

    if (searchPart) {
      if (currentSearch) {
        searchPart.textContent = `for "${currentSearch}"`;
        searchPart.classList.remove('hidden');
      } else {
        searchPart.textContent = '';
        searchPart.classList.add('hidden');
      }
    }

    if (catPart) {
      if (currentCategory && currentCategory !== 'all' && currentCategoryName) {
        catPart.textContent = `in ${currentCategoryName}`;
        catPart.classList.remove('hidden');
      } else {
        catPart.textContent = '';
        catPart.classList.add('hidden');
      }
    }
  }

  function updateUrl() {
    const params = new URLSearchParams();
    if (currentCategory && currentCategory !== 'all') {
      params.set('category', currentCategory);
    }
    if (currentSearch) {
      params.set('search', currentSearch);
    }
    const qs = params.toString();
    const newUrl = endpointUrl + (qs ? '?' + qs : '');
    window.history.replaceState({ category: currentCategory, search: currentSearch }, '', newUrl);
  }

  function fetchAndRender() {
    if (abortCtrl) {
      abortCtrl.abort();
    }
    abortCtrl = new AbortController();

    const params = new URLSearchParams();
    if (currentCategory) params.set('category', currentCategory);
    if (currentSearch) params.set('search', currentSearch);

    fetch(endpointUrl + '?' + params.toString(), {
      signal: abortCtrl.signal,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    })
    .then(function (res) {
      if (!res.ok) throw new Error('Network error');
      return res.json();
    })
    .then(function (data) {
      if (productsGrid) {
        const products = data.products || [];
        const total    = data.total ?? products.length;

        if (data.active_category_name) {
          currentCategoryName = data.active_category_name;
        }

        updateResultsHeader(total);

        if (products.length === 0) {
          productsGrid.innerHTML = renderEmptyState();
        } else {
          let html = '';
          for (let i = 0; i < products.length; i++) {
            html += renderProductCard(products[i], i);
          }
          productsGrid.innerHTML = html;
          if (typeof window.fnSyncAllCompareButtons === 'function') {
            window.fnSyncAllCompareButtons();
          }
        }
      }

      if (paginationWrap) {
        paginationWrap.classList.toggle('hidden', !data.has_pages);
      }

      updateUrl();
    })
    .catch(function (err) {
      if (err.name === 'AbortError') return;
    });
  }

  // ── Tab click handler ──
  if (tabsRow) {
    tabsRow.addEventListener('click', function (e) {
      const btn = e.target.closest('.category-tab-btn');
      if (!btn) return;

      e.preventDefault();

      const newCat  = btn.dataset.category || 'all';
      const newName = btn.dataset.name || 'All Categories';

      if (newCat === currentCategory) return;

      // Update active styling across tabs
      tabsRow.querySelectorAll('.category-tab-btn').forEach(function (b) {
        b.classList.remove('active');
      });
      btn.classList.add('active');

      currentCategory     = newCat;
      currentCategoryName = newName;

      fetchAndRender();
    });
  }

  // ── Hero search input handler (Live filtering below) ──
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const val = this.value.trim();
      currentSearch = val;

      if (searchClear) {
        searchClear.classList.toggle('hidden', val.length === 0);
      }

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        fetchAndRender();
      }, 180);
    });

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        currentSearch = this.value.trim();
        fetchAndRender();
      }
    });
  }

  // ── Hero search clear button handler ──
  if (searchClear) {
    searchClear.addEventListener('click', function () {
      if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
      }
      searchClear.classList.add('hidden');
      currentSearch = '';
      clearTimeout(debounceTimer);
      fetchAndRender();
    });
  }

  // ── Delegated listener for "Clear filters" in empty state ──
  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'btn-clear-all-filters') {
      e.preventDefault();

      currentSearch       = '';
      currentCategory     = 'all';
      currentCategoryName = 'All Categories';

      if (searchInput) {
        searchInput.value = '';
      }
      if (searchClear) {
        searchClear.classList.add('hidden');
      }

      if (tabsRow) {
        tabsRow.querySelectorAll('.category-tab-btn').forEach(function (b) {
          b.classList.toggle('active', b.dataset.category === 'all');
        });
      }

      fetchAndRender();
    }
  });

  // ── Browser back/forward navigation sync ──
  window.addEventListener('popstate', function (e) {
    const params = new URLSearchParams(window.location.search);
    currentCategory = params.get('category') || 'all';
    currentSearch   = params.get('search') || '';

    if (searchInput) {
      searchInput.value = currentSearch;
      if (searchClear) {
        searchClear.classList.toggle('hidden', currentSearch.length === 0);
      }
    }

    if (tabsRow) {
      tabsRow.querySelectorAll('.category-tab-btn').forEach(function (b) {
        const matches = (b.dataset.category || 'all') === currentCategory;
        b.classList.toggle('active', matches);
        if (matches) currentCategoryName = b.dataset.name || 'All Categories';
      });
    }

    fetchAndRender();
  });
})();
</script>
@endpush
