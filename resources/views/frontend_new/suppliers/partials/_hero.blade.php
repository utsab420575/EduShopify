{{-- Hero Section for Suppliers directory --}}
<section class="bg-gray-900 py-12 px-4">
  <div class="max-w-7xl mx-auto">
    <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest mb-2">Directory</p>
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight mb-2">Suppliers</h1>
    <p class="text-gray-400 text-base mb-8">Browse verified education suppliers from around the world</p>

    {{-- Live Search --}}
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

      {{-- Live search dropdown results --}}
      <div
        id="sup-search-dropdown"
        class="hidden absolute z-50 left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 max-h-96 overflow-y-auto"
      >
        <div id="sup-search-results" class="p-2"></div>
        <a
          id="sup-search-see-all"
          href="#"
          class="hidden block text-center text-sm text-emerald-600 font-medium py-3 border-t border-gray-100 hover:bg-gray-50"
        >See all results</a>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  const input    = document.getElementById('sup-search-input');
  const wrap     = document.getElementById('sup-search-wrap');
  const dropdown = document.getElementById('sup-search-dropdown');
  const results  = document.getElementById('sup-search-results');
  const seeAll   = document.getElementById('sup-search-see-all');
  const clearBtn = document.getElementById('sup-search-clear');

  if (!input) return;

  let debounceTimer = null;
  const searchUrl = '{{ route('v2.suppliers.index') }}';

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderResults(suppliers, total, query) {
    results.innerHTML = '';
    if (suppliers.length === 0) {
      results.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">No suppliers found for &ldquo;' + escapeHtml(query) + '&rdquo;</p>';
      seeAll.classList.add('hidden');
      dropdown.classList.remove('hidden');
      return;
    }

    const countEl = document.createElement('p');
    countEl.className = 'text-xs text-gray-400 px-3 py-1';
    countEl.textContent = total + ' result' + (total !== 1 ? 's' : '') + ' found';
    results.appendChild(countEl);

    suppliers.slice(0, 6).forEach(function (s) {
      const a = document.createElement('a');
      a.href = s.url;
      a.className = 'flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition group';

      const imgWrap = document.createElement('div');
      imgWrap.className = 'w-10 h-10 rounded-full bg-emerald-50 overflow-hidden flex-shrink-0 flex items-center justify-center text-emerald-600 font-bold text-sm';
      if (s.image) {
        const img = document.createElement('img');
        img.src = s.image;
        img.alt = s.name;
        img.className = 'w-full h-full object-cover';
        imgWrap.appendChild(img);
      } else {
        imgWrap.textContent = s.name.charAt(0).toUpperCase();
      }
      a.appendChild(imgWrap);

      const info = document.createElement('div');
      info.className = 'flex-1 min-w-0';
      info.innerHTML = '<p class="text-sm font-semibold text-gray-900 truncate">' + escapeHtml(s.name) + '</p>'
        + '<p class="text-xs text-gray-400 truncate">' + escapeHtml(s.type || '') + '</p>';
      a.appendChild(info);

      const arrow = document.createElement('span');
      arrow.className = 'text-xs text-emerald-600 font-medium flex-shrink-0';
      arrow.textContent = 'View →';
      a.appendChild(arrow);

      results.appendChild(a);
    });

    if (total > 6) {
      seeAll.href = searchUrl + '?search=' + encodeURIComponent(query);
      seeAll.textContent = 'See all ' + total + ' results';
      seeAll.classList.remove('hidden');
    } else {
      seeAll.classList.add('hidden');
    }

    dropdown.classList.remove('hidden');
  }

  function doSearch(query) {
    if (query.length < 2) {
      dropdown.classList.add('hidden');
      return;
    }
    fetch(searchUrl + '?search=' + encodeURIComponent(query) + '&category=all', {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      renderResults(data.suppliers || [], data.total || 0, query);
    })
    .catch(function () {});
  }

  input.addEventListener('input', function () {
    const q = this.value.trim();
    clearBtn.classList.toggle('hidden', q.length === 0);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () { doSearch(q); }, 350);
  });

  clearBtn.addEventListener('click', function () {
    input.value = '';
    clearBtn.classList.add('hidden');
    dropdown.classList.add('hidden');
    input.focus();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') dropdown.classList.add('hidden');
  });

  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) dropdown.classList.add('hidden');
  });
})();
</script>
@endpush
