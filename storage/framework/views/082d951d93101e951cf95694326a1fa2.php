
<style>
  /* ── Tab Buttons Styling (matches category tabs) ── */
  .category-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 1rem;
    font-size: 0.75rem;
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

  .category-tab-btn.active {
    background-color: #0f172a; /* slate-900 */
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  .category-tab-btn:not(.active) {
    background-color: #ffffff;
    color: #4b5563;
    border-color: #e5e7eb;
  }

  .category-tab-btn:not(.active):hover {
    border-color: #10b981;
    color: #059669;
    background-color: #f0fdf4;
  }

  @keyframes blogCardFadeUp {
    0% {
      opacity: 0;
      transform: translateY(18px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .blog-card-fade-up {
    animation: blogCardFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    will-change: transform, opacity;
  }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
  
  <div class="overflow-x-auto pb-3 mb-6 -mx-4 px-4 scrollbar-none">
    <div class="flex items-center gap-2 min-w-max" id="blog-tabs-container">
      <button
        type="button"
        class="category-tab-btn <?php echo e($activeCategory === 'all' ? 'active' : ''); ?>"
        data-category="all"
        data-name="All Articles"
      >
        All Articles (<?php echo e($totalArticles); ?>)
      </button>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button
          type="button"
          class="category-tab-btn <?php echo e($activeCategory === $category->slug ? 'active' : ''); ?>"
          data-category="<?php echo e($category->slug); ?>"
          data-name="<?php echo e($category->name); ?>"
        >
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->icon): ?>
            <i class="<?php echo e($category->icon); ?> mr-1.5 text-xs opacity-80"></i>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php echo e($category->name); ?> (<?php echo e($category->post_count); ?>)
        </button>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>

  
  <div class="flex items-center justify-between mb-6 text-sm text-gray-500">
    <div id="blog-results-summary">
      Showing <span id="blog-results-count" class="font-bold text-gray-900"><?php echo e($articles->total()); ?></span> articles
      <span id="blog-active-cat-label" class="font-medium text-emerald-600">
        <?php echo e($activeCategory !== 'all' ? 'in ' . ($categories->firstWhere('slug', $activeCategory)?->name ?? 'Category') : ''); ?>

      </span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
        matching "<span id="blog-active-search-label" class="text-gray-900 font-semibold"><?php echo e($search); ?></span>"
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>

  
  <div id="blog-loading-skeleton" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 6; $i++): ?>
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden p-0 animate-pulse">
        <div class="h-48 bg-gray-200 w-full"></div>
        <div class="p-5 space-y-3">
          <div class="h-3 bg-gray-200 rounded w-1/3"></div>
          <div class="h-5 bg-gray-200 rounded w-4/5"></div>
          <div class="h-3 bg-gray-200 rounded w-full"></div>
          <div class="h-3 bg-gray-200 rounded w-2/3"></div>
          <div class="pt-3 border-t border-gray-100 flex justify-between">
            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
          </div>
        </div>
      </div>
    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <div
    id="blog-grid"
    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 <?php echo e($articles->isEmpty() ? 'hidden' : ''); ?>"
  >
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $authorName = $article->account?->display_name
            ?? $article->account?->supplierProfile?->display_name
            ?? $article->account?->buyerProfile?->display_name
            ?? 'EduShopify Member';
        $cover = $article->coverImageUrl();
      ?>

      <a href="<?php echo e(route('v2.blogs.show', $article->slug)); ?>"
         class="blog-card group block bg-white rounded-2xl border border-gray-200/90 hover:border-emerald-300 hover:shadow-xl transition-all duration-200 overflow-hidden flex flex-col blog-card-fade-up"
         style="animation-delay: <?php echo e($loop->index * 45); ?>ms">
        
        
        <div class="relative h-48 overflow-hidden bg-gray-100 shrink-0">
          <img
            src="<?php echo e($cover); ?>"
            alt="<?php echo e($article->title); ?>"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>

          
          <span class="absolute top-3 left-3 bg-emerald-600/90 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full shadow-sm">
            <?php echo e($article->category?->name ?? 'Article'); ?>

          </span>

          
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->featured): ?>
            <span class="absolute top-3 right-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
              <span>★</span> Featured
            </span>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="p-5 flex-1 flex flex-col justify-between">
          <div>
            
            <div class="flex items-center gap-2 mb-2.5 text-[11px] text-gray-500">
              <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                <?php echo e(strtoupper(substr($authorName, 0, 1))); ?>

              </div>
              <span class="font-medium text-gray-700 truncate max-w-[130px]"><?php echo e($authorName); ?></span>
              <span class="text-gray-300">•</span>
              <span class="text-gray-400 whitespace-nowrap"><?php echo e($article->published_at?->format('M d, Y')); ?></span>
            </div>

            
            <h2 class="font-bold text-base text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug mb-2">
              <?php echo e($article->title); ?>

            </h2>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->excerpt): ?>
              <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-4">
                <?php echo e($article->excerpt); ?>

              </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-3 text-[11px] text-gray-400">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <?php echo e($article->reading_time_minutes ?? 5); ?>m read
              </span>

              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->likes_count > 0): ?>
                <span class="flex items-center gap-1 text-gray-400">
                  <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <?php echo e($article->likes_count); ?>

                </span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->comments_count > 0): ?>
                <span class="flex items-center gap-1 text-gray-400">
                  <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                  </svg>
                  <?php echo e($article->comments_count); ?>

                </span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 group-hover:text-emerald-700 transition-colors">
              Read Article
              <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </span>
          </div>

        </div>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <div
    id="blog-empty-state"
    class="text-center py-16 px-4 bg-white rounded-2xl border border-gray-200 <?php echo e($articles->isEmpty() ? '' : 'hidden'); ?>"
  >
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
      </svg>
    </div>
    <h3 class="text-lg font-bold text-gray-900 mb-1">No articles found</h3>
    <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
      We couldn't find any articles matching your search criteria. Try using different keywords or browsing all categories.
    </p>
    <button
      id="blog-reset-filter-btn"
      type="button"
      class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      View All Articles
    </button>
  </div>

  
  <div id="blog-pagination" class="mt-8 <?php echo e($articles->hasPages() ? '' : 'hidden'); ?>">
    <?php echo e($articles->links()); ?>

  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
  let currentCategory = <?php echo json_encode($activeCategory, 15, 512) ?>;
  let currentSearch = <?php echo json_encode($search, 15, 512) ?>;
  let searchDebounceTimer = null;

  const searchInput    = document.getElementById('blog-search-input');
  const searchClearBtn = document.getElementById('blog-search-clear');
  const tabsContainer  = document.getElementById('blog-tabs-container');
  const gridContainer  = document.getElementById('blog-grid');
  const skeleton       = document.getElementById('blog-loading-skeleton');
  const emptyState     = document.getElementById('blog-empty-state');
  const resultsCount   = document.getElementById('blog-results-count');
  const activeCatLabel = document.getElementById('blog-active-cat-label');
  const summaryWrap    = document.getElementById('blog-results-summary');
  const paginationWrap = document.getElementById('blog-pagination');
  const resetBtn       = document.getElementById('blog-reset-filter-btn');

  function updateSearchClearVisibility() {
    if (searchClearBtn) {
      if (searchInput && searchInput.value.trim().length > 0) {
        searchClearBtn.classList.remove('hidden');
      } else {
        searchClearBtn.classList.add('hidden');
      }
    }
  }

  function renderArticles(articles) {
    if (!articles || articles.length === 0) {
      gridContainer.innerHTML = '';
      gridContainer.classList.add('hidden');
      emptyState.classList.remove('hidden');
      if (paginationWrap) paginationWrap.classList.add('hidden');
      return;
    }

    emptyState.classList.add('hidden');
    gridContainer.classList.remove('hidden');

    let html = '';
    articles.forEach(function (article, index) {
      const delay = index * 45;
      const featuredBadge = article.featured
        ? `<span class="absolute top-3 right-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1"><span>★</span> Featured</span>`
        : '';

      const authorInitial = (article.author || 'E').charAt(0).toUpperCase();

      const likesHtml = article.likes_count > 0
        ? `<span class="flex items-center gap-1 text-gray-400">
             <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
             ${article.likes_count}
           </span>`
        : '';

      const commentsHtml = article.comments_count > 0
        ? `<span class="flex items-center gap-1 text-gray-400">
             <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
             ${article.comments_count}
           </span>`
        : '';

      html += `
        <a href="${article.url}"
           class="blog-card group block bg-white rounded-2xl border border-gray-200/90 hover:border-emerald-300 hover:shadow-xl transition-all duration-200 overflow-hidden flex flex-col blog-card-fade-up"
           style="animation-delay: ${delay}ms">
          <div class="relative h-48 overflow-hidden bg-gray-100 shrink-0">
            <img src="${article.cover_image}" alt="${article.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>
            <span class="absolute top-3 left-3 bg-emerald-600/90 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full shadow-sm">
              ${article.category}
            </span>
            ${featuredBadge}
          </div>
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2.5 text-[11px] text-gray-500">
                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                  ${authorInitial}
                </div>
                <span class="font-medium text-gray-700 truncate max-w-[130px]">${article.author}</span>
                <span class="text-gray-300">•</span>
                <span class="text-gray-400 whitespace-nowrap">${article.date}</span>
              </div>
              <h2 class="font-bold text-base text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug mb-2">
                ${article.title}
              </h2>
              ${article.excerpt ? `<p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-4">${article.excerpt}</p>` : ''}
            </div>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-auto">
              <div class="flex items-center gap-3 text-[11px] text-gray-400">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  ${article.reading_time}m read
                </span>
                ${likesHtml}
                ${commentsHtml}
              </div>
              <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 group-hover:text-emerald-700 transition-colors">
                Read Article
                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </span>
            </div>
          </div>
        </a>
      `;
    });

    gridContainer.innerHTML = html;
  }

  function fetchArticles() {
    // Show skeleton
    gridContainer.classList.add('hidden');
    emptyState.classList.add('hidden');
    skeleton.classList.remove('hidden');

    const params = new URLSearchParams();
    if (currentCategory && currentCategory !== 'all') {
      params.set('category', currentCategory);
    }
    if (currentSearch) {
      params.set('search', currentSearch);
    }

    const url = '<?php echo e(route("v2.blogs.index")); ?>?' + params.toString();

    // Update browser URL silently
    history.pushState(null, '', url);

    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      skeleton.classList.add('hidden');

      if (resultsCount) resultsCount.textContent = data.total;
      if (activeCatLabel) {
        activeCatLabel.textContent = data.active_category !== 'all' ? `in ${data.active_category_name}` : '';
      }

      renderArticles(data.articles);
    })
    .catch(function (err) {
      console.error('Error fetching blog articles:', err);
      skeleton.classList.add('hidden');
      gridContainer.classList.remove('hidden');
    });
  }

  // Category Tab Click Handler
  if (tabsContainer) {
    tabsContainer.addEventListener('click', function (e) {
      const btn = e.target.closest('.category-tab-btn');
      if (!btn) return;
      e.preventDefault();

      tabsContainer.querySelectorAll('.category-tab-btn').forEach(function (t) {
        t.classList.remove('active');
      });
      btn.classList.add('active');

      currentCategory = btn.getAttribute('data-category');
      fetchArticles();
    });
  }

  // Search Input Handler (Debounced 300ms)
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      updateSearchClearVisibility();
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(function () {
        currentSearch = searchInput.value.trim();
        fetchArticles();
      }, 300);
    });
  }

  // Clear Search Handler
  if (searchClearBtn) {
    searchClearBtn.addEventListener('click', function () {
      if (searchInput) {
        searchInput.value = '';
        currentSearch = '';
        updateSearchClearVisibility();
        fetchArticles();
      }
    });
  }

  // Reset Filter Button Handler
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      currentCategory = 'all';
      currentSearch = '';
      if (searchInput) searchInput.value = '';
      updateSearchClearVisibility();

      if (tabsContainer) {
        tabsContainer.querySelectorAll('.category-tab-btn').forEach(function (t) {
          t.classList.remove('active');
        });
        const allBtn = tabsContainer.querySelector('[data-category="all"]');
        if (allBtn) allBtn.classList.add('active');
      }

      fetchArticles();
    });
  }

  updateSearchClearVisibility();
});
</script>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/blogs/partials/_content.blade.php ENDPATH**/ ?>