
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

  
  <div class="flex items-center gap-2 flex-wrap mb-6" id="category-tabs-row">
    <a
      href="<?php echo e(route('v2.categories.index')); ?>"
      data-category="all"
      data-name="All Categories"
      class="category-tab-btn <?php echo e($activeCategory === 'all' ? 'active' : ''); ?>"
    >
      All Categories
    </a>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cat->listing_count > 0): ?>
        <a
          href="<?php echo e(route('v2.categories.index', array_filter(['category' => $cat->slug, 'search' => $search]))); ?>"
          data-category="<?php echo e($cat->slug); ?>"
          data-name="<?php echo e($cat->name); ?>"
          class="category-tab-btn <?php echo e($activeCategory === $cat->slug ? 'active' : ''); ?>"
        >
          <?php echo e($cat->name); ?>

        </a>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <p id="cat-results-count" class="text-sm text-gray-500 mb-5 transition-opacity duration-200">
    <span class="font-semibold text-gray-900" id="cat-count-num"><?php echo e(number_format($totalProducts)); ?></span>
    <span id="cat-count-label"><?php echo e(Str::plural('product', $totalProducts)); ?> found</span>
    <span id="cat-count-search-part">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?> for "<span class="text-emerald-600 font-medium"><?php echo e($search); ?></span>"<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>
    <span id="cat-count-cat-part">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCategory && $activeCategory !== 'all'): ?> in <span class="text-emerald-600 font-medium"><?php echo e($categories->firstWhere('slug', $activeCategory)?->name); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>
  </p>

  
  <div id="cat-products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $productUrl = route('v2.products.show', $listing->slug);
        $img        = $listing->primaryImage?->getUrl()
                   ?? $listing->getFirstMediaUrl('gallery');
        $brand      = $listing->brand?->name
                   ?? $listing->supplierAccount?->display_name
                   ?? '—';
        $catName    = $listing->mainCategory?->name ?? 'Other';
        $price      = $listing->base_price;
        $currency   = $listing->currency_code ?? 'USD';
        $unit       = $listing->unit?->abbreviation ?? $listing->unit?->symbol ?? '';
      ?>
      <a
        href="<?php echo e($productUrl); ?>"
        class="group block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md hover:border-emerald-200 transition-all duration-200 product-card-fade-up"
        style="animation-delay: <?php echo e($loop->index * 55); ?>ms"
      >
        
        <div class="relative h-48 bg-gray-100 overflow-hidden">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($img): ?>
            <img
              src="<?php echo e($img); ?>"
              alt="<?php echo e($listing->name); ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          <?php else: ?>
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
              <svg class="w-12 h-12 mb-1" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9l4.5-4.5 4.5 4.5 4.5-4.5 4.5 4.5"/>
                <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor"/>
              </svg>
              <span class="text-xs">No image</span>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->is_featured): ?>
            <span class="absolute top-2 left-2 bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">Featured</span>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="p-3.5">
          <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mb-1 truncate"><?php echo e($catName); ?></p>
          <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-0.5 line-clamp-2 group-hover:text-emerald-700 transition-colors"><?php echo e($listing->name); ?></h3>
          <p class="text-xs text-gray-500 mb-3 truncate"><?php echo e($brand); ?></p>

          <div class="flex items-center justify-between">
            <div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($price): ?>
                <p class="text-sm font-bold text-emerald-600">
                  <?php echo e($currency); ?> <?php echo e(number_format($price, 0)); ?>

                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit): ?> <span class="text-xs font-normal text-gray-400">/ <?php echo e($unit); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
              <?php else: ?>
                <p class="text-xs text-gray-400 italic">Price on request</p>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <span class="text-xs text-emerald-600 font-semibold group-hover:underline">View →</span>
          </div>
        </div>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <div id="cat-pagination-wrap" class="mt-10 flex justify-center <?php echo e($listings->hasPages() ? '' : 'hidden'); ?>">
    <?php echo e($listings->links('pagination::simple-tailwind')); ?>

  </div>

</main>

<?php $__env->startPush('scripts'); ?>
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

  const endpointUrl    = '<?php echo e(route('v2.categories.index')); ?>';

  // Current state
  let currentCategory     = '<?php echo e($activeCategory); ?>' || 'all';
  let currentCategoryName = '<?php echo e($activeCategory === "all" ? "All Categories" : ($categories->firstWhere("slug", $activeCategory)?->name ?? "")); ?>';
  let currentSearch       = '<?php echo e($search); ?>' || '';
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

    const featuredHtml = p.is_featured
      ? `<span class="absolute top-2 left-2 bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">Featured</span>`
      : '';

    return `
      <a
        href="${escapeHtml(p.url)}"
        class="group block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md hover:border-emerald-200 transition-all duration-200 product-card-fade-up"
        style="animation-delay: ${index * 55}ms"
      >
        <div class="relative h-48 bg-gray-100 overflow-hidden">
          ${imgHtml}
          ${featuredHtml}
        </div>
        <div class="p-3.5">
          <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mb-1 truncate">${escapeHtml(p.category || 'Other')}</p>
          <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-0.5 line-clamp-2 group-hover:text-emerald-700 transition-colors">${escapeHtml(p.name)}</h3>
          <p class="text-xs text-gray-500 mb-3 truncate">${escapeHtml(p.brand || '—')}</p>
          <div class="flex items-center justify-between">
            <div>${priceHtml}</div>
            <span class="text-xs text-emerald-600 font-semibold group-hover:underline">View &rarr;</span>
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
    if (countNum) countNum.textContent = Number(total).toLocaleString();
    if (countLabel) countLabel.textContent = (total === 1 ? 'product' : 'products') + ' found';

    if (searchPart) {
      searchPart.innerHTML = currentSearch
        ? ` for "<span class="text-emerald-600 font-medium">${escapeHtml(currentSearch)}</span>"`
        : '';
    }

    if (catPart) {
      catPart.innerHTML = (currentCategory && currentCategory !== 'all' && currentCategoryName)
        ? ` in <span class="text-emerald-600 font-medium">${escapeHtml(currentCategoryName)}</span>`
        : '';
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
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\categories\partials\_content.blade.php ENDPATH**/ ?>