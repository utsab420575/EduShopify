{{-- Hero Section for Suppliers directory --}}
<section class="bg-gray-900 py-12 px-4">
  <div class="max-w-7xl mx-auto">
    <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest mb-2">Directory</p>
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight mb-2">Suppliers</h1>
    <p class="text-gray-400 text-base mb-8">Browse verified education suppliers from around the world</p>

    {{-- Live Search — filters the results section below as you type,
         no Enter needed. The currently active category tab (read from
         the results section's own data attribute) is preserved. --}}
    <div class="relative max-w-xl" id="sup-search-wrap">
      <div class="flex items-center bg-white rounded-xl shadow-sm overflow-hidden">
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
          value="{{ $search }}"
          class="flex-1 px-4 py-3.5 text-sm text-gray-800 placeholder-gray-400 bg-transparent focus:outline-none"
        />
        <button
          id="sup-search-clear"
          type="button"
          class="pr-4 text-gray-400 hover:text-gray-600 {{ $search ? '' : 'hidden' }}"
          aria-label="Clear search"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  const input    = document.getElementById('sup-search-input');
  const wrap     = document.getElementById('sup-search-wrap');
  const clearBtn = document.getElementById('sup-search-clear');

  if (!input) return;

  let debounceTimer = null;
  let currentRequest = null;
  const baseUrl = '{{ route('v2.suppliers.index') }}';

  function activeCategory() {
    const content = document.getElementById('supplier-content');
    return content ? content.dataset.activeCategory : 'all';
  }

  function runSearch(query) {
    if (currentRequest) currentRequest.abort();
    currentRequest = new AbortController();

    const url = baseUrl + '?search=' + encodeURIComponent(query) + '&category=' + encodeURIComponent(activeCategory());

    fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: currentRequest.signal,
    })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        const content = document.getElementById('supplier-content');
        if (content) content.outerHTML = html;
      })
      .catch(function (e) { if (e.name !== 'AbortError') { /* leave current results as-is */ } });
  }

  input.addEventListener('input', function () {
    const q = this.value.trim();
    clearBtn.classList.toggle('hidden', q.length === 0);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () { runSearch(q); }, 350);
  });

  clearBtn.addEventListener('click', function () {
    input.value = '';
    clearBtn.classList.add('hidden');
    runSearch('');
    input.focus();
  });
})();
</script>
@endpush
