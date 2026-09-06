
<section class="bg-gray-900 py-12 px-4">
  <div class="max-w-7xl mx-auto">
    <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest mb-2">Directory</p>
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight mb-2">Suppliers</h1>
    <p class="text-gray-400 text-base mb-8">Browse verified education suppliers from around the world</p>

    
    <div class="relative max-w-xl" id="sup-search-wrap">
      <div class="flex items-center bg-white rounded-xl shadow-sm overflow-hidden border border-transparent focus-within:border-emerald-500 transition-colors">
        <span class="pl-4 text-gray-400 flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </span>
        <input
          id="sup-search-input"
          type="text"
          name="search"
          placeholder="Search suppliers..."
          autocomplete="off"
          value="<?php echo e($search); ?>"
          class="flex-1 px-4 py-3.5 text-sm text-gray-800 placeholder-gray-400 bg-transparent focus:outline-none"
        />
        <button
          id="sup-search-clear"
          type="button"
          class="pr-4 text-gray-400 hover:text-gray-600 transition-colors <?php echo e($search ? '' : 'hidden'); ?>"
          aria-label="Clear search"
          title="Clear search"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
  const input    = document.getElementById('sup-search-input');
  const clearBtn = document.getElementById('sup-search-clear');

  let debounceTimer = null;
  let currentRequest = null;
  const baseUrl = '<?php echo e(route('v2.suppliers.index')); ?>';

  function activeCategory() {
    const content = document.getElementById('supplier-content');
    return content ? (content.dataset.activeCategory || 'all') : 'all';
  }

  function runFilter(query, category) {
    if (currentRequest) currentRequest.abort();
    currentRequest = new AbortController();

    const cat = category !== undefined ? category : activeCategory();
    const params = new URLSearchParams();
    if (query) params.set('search', query);
    if (cat && cat !== 'all') params.set('category', cat);

    const qs = params.toString();
    const url = baseUrl + (qs ? '?' + qs : '');

    fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: currentRequest.signal,
    })
      .then(function (r) {
        if (!r.ok) throw new Error('Network error');
        return r.text();
      })
      .then(function (html) {
        const content = document.getElementById('supplier-content');
        if (content) {
          content.outerHTML = html;
        }
        window.history.replaceState({ category: cat, search: query }, '', url);
      })
      .catch(function (e) {
        if (e.name !== 'AbortError') { /* error ignored */ }
      });
  }

  if (input) {
    input.addEventListener('input', function () {
      const q = this.value.trim();
      if (clearBtn) clearBtn.classList.toggle('hidden', q.length === 0);
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () { runFilter(q); }, 200);
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        runFilter(this.value.trim());
      }
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (input) {
        input.value = '';
        input.focus();
      }
      clearBtn.classList.add('hidden');
      clearTimeout(debounceTimer);
      runFilter('');
    });
  }

  // Delegated click handler for category tabs and clear-all
  document.addEventListener('click', function (e) {
    const tabBtn = e.target.closest('.category-tab-btn');
    if (tabBtn) {
      e.preventDefault();
      const cat = tabBtn.dataset.category || 'all';
      const q = input ? input.value.trim() : '';
      runFilter(q, cat);
      return;
    }

    const clearAll = e.target.closest('#btn-clear-all-filters');
    if (clearAll) {
      e.preventDefault();
      if (input) input.value = '';
      if (clearBtn) clearBtn.classList.add('hidden');
      runFilter('', 'all');
      return;
    }
  });

  window.addEventListener('popstate', function () {
    const params = new URLSearchParams(window.location.search);
    const cat = params.get('category') || 'all';
    const q = params.get('search') || '';
    if (input) {
      input.value = q;
      if (clearBtn) clearBtn.classList.toggle('hidden', q.length === 0);
    }
    runFilter(q, cat);
  });
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\suppliers\partials\_hero.blade.php ENDPATH**/ ?>