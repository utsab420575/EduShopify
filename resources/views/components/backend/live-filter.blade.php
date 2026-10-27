{{--
    AJAX live filter bar per design.md §17.1 — debounced search, multi-select
    dropdown checkbox filters with live per-option counts, an applied-filter
    pill strip and a spinner. Every change re-fetches the table partial into
    #{id}-results (sent with X-Requested-With so the controller's
    liveFilterResponse() answers with JSON) — nothing to "Apply". Sort and
    pagination links inside the results that carry data-ajax-link (or sit
    inside a [data-ajax-link] wrapper) go through the same fetch.

    filters: ['status' => ['label' => 'Status', 'options' => [value => label], 'selected' => [...]], ...]
    counts:  ['status' => [value => n], ...] — refreshed from each JSON response.
--}}
@props([
    'id', 'action', 'search' => '', 'searchParam' => 'search', 'placeholder' => 'Search...',
    'filters' => [], 'counts' => [],
])
@php
    $hasActive = $search !== '' || collect($filters)->contains(fn ($f) => ! empty($f['selected']));
@endphp
<form id="{{ $id }}-filter-form" method="GET" action="{{ $action }}" data-live-filter-form data-results="{{ $id }}-results" data-search-param="{{ $searchParam }}"
      class="bg-white rounded-xl border border-gray-200 p-3.5 mb-4 shadow-xs">
    <div class="flex flex-wrap items-center gap-2.5">
        <div class="relative flex-1 min-w-[220px] sm:min-w-[300px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" name="{{ $searchParam }}" value="{{ $search }}" placeholder="{{ $placeholder }}" autocomplete="off" data-live-search
                   class="w-full text-xs pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white placeholder-gray-400">
        </div>

        @foreach($filters as $name => $filter)
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-52 shrink-0" data-filter-group="{{ $name }}" data-filter-label="{{ $filter['label'] }}">
                <button type="button" @click="open = !open"
                        class="w-full text-xs font-semibold px-3 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-between gap-1.5 transition">
                    <span class="flex items-center gap-1.5 truncate">
                        <span>{{ $filter['label'] }}</span>
                        <span data-filter-badge class="{{ empty($filter['selected']) ? 'hidden' : '' }} bg-indigo-600 text-white rounded-full text-[10px] px-1.5 font-bold leading-tight">{{ count($filter['selected']) }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute z-20 mt-2 w-60 bg-white border border-gray-200 rounded-lg shadow-lg p-3 left-0">
                    <div class="space-y-1.5 max-h-64 overflow-y-auto">
                        @foreach($filter['options'] as $value => $label)
                            <label class="flex items-center justify-between gap-2 text-xs text-gray-700 cursor-pointer hover:bg-gray-50 p-1 rounded transition">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" data-live-filter data-label="{{ $label }}"
                                           @checked(in_array((string) $value, $filter['selected'], true))
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $label }}
                                </span>
                                <span class="text-gray-400 text-[11px]" data-count="{{ $name }}.{{ $value }}">{{ $counts[$name][$value] ?? 0 }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                        <button type="button" data-clear-group="{{ $name }}" class="text-[11px] font-semibold text-gray-500 hover:text-red-600">Clear</button>
                        <span class="text-[11px] text-gray-400">Updates instantly</span>
                    </div>
                </div>
            </div>
        @endforeach

        <span data-live-spinner class="hidden text-xs text-indigo-600 items-center gap-1.5 shrink-0 font-medium">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Updating…
        </span>
    </div>

    <div data-active-filters class="{{ $hasActive ? '' : 'hidden' }} pt-3 mt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-medium text-gray-500 mr-1 flex items-center gap-1">
                <i class="fa-solid fa-filter text-[10px] text-gray-400"></i> Applied:
            </span>
            <div data-active-pills class="flex flex-wrap items-center gap-1.5"></div>
        </div>
        <button type="button" data-clear-all class="text-xs font-semibold text-red-600 hover:text-red-800 hover:underline flex items-center gap-1 cursor-pointer">
            <i class="fa-solid fa-trash-can text-[11px]"></i> Clear all
        </button>
    </div>
</form>

@once
@push('scripts')
<script>
(function () {
    // One colour per filter type so pills are distinguishable at a glance
    // (search first, then each dropdown in order). Full class strings so
    // Tailwind's scanner picks them up.
    const PILL_STYLES = [
        'bg-indigo-50 text-indigo-700 border-indigo-200',
        'bg-emerald-50 text-emerald-700 border-emerald-200',
        'bg-amber-50 text-amber-700 border-amber-200',
        'bg-purple-50 text-purple-700 border-purple-200',
        'bg-rose-50 text-rose-700 border-rose-200',
    ];

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function initLiveFilter(form) {
        const results = document.getElementById(form.dataset.results);
        const spinner = form.querySelector('[data-live-spinner]');
        const searchInput = form.querySelector('[data-live-search]');
        const searchParam = form.dataset.searchParam;
        const groups = Array.from(form.querySelectorAll('[data-filter-group]'));

        let debounceTimer = null;
        let currentRequest = null;

        function pill(style, text, type, value) {
            const el = document.createElement('span');
            el.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border shadow-2xs ' + style;
            el.innerHTML = '<span>' + text + '</span>'
                + '<button type="button" class="hover:text-red-600 opacity-70 hover:opacity-100 cursor-pointer transition" data-remove-filter="' + escapeHtml(type) + '"'
                + (value !== undefined ? ' data-value="' + escapeHtml(value) + '"' : '') + ' title="Remove filter">'
                + '<i class="fa-solid fa-xmark text-[11px]"></i></button>';
            return el;
        }

        function renderAppliedFilters() {
            const pills = form.querySelector('[data-active-pills]');
            const query = searchInput.value.trim();
            let active = query !== '';

            pills.innerHTML = '';
            if (query !== '') {
                pills.appendChild(pill(PILL_STYLES[0], 'Search: <strong>"' + escapeHtml(query) + '"</strong>', 'search'));
            }

            groups.forEach(function (group, i) {
                const checked = Array.from(group.querySelectorAll('input[data-live-filter]:checked'));
                const badge = group.querySelector('[data-filter-badge]');
                badge.textContent = checked.length;
                badge.classList.toggle('hidden', checked.length === 0);

                checked.forEach(function (cb) {
                    active = true;
                    pills.appendChild(pill(
                        PILL_STYLES[(i + 1) % PILL_STYLES.length],
                        escapeHtml(group.dataset.filterLabel) + ': <strong>' + escapeHtml(cb.dataset.label) + '</strong>',
                        group.dataset.filterGroup,
                        cb.value
                    ));
                });
            });

            form.querySelector('[data-active-filters]').classList.toggle('hidden', !active);
        }

        function applyCounts(counts) {
            form.querySelectorAll('[data-count]').forEach(function (el) {
                const [group, bucket] = el.getAttribute('data-count').split('.');
                el.textContent = (counts && counts[group] && counts[group][bucket]) || 0;
            });
        }

        // The form is the single source of truth: responses only replace the
        // table and counts, never the inputs. Writing the request URL's search
        // value back into the box would clobber whatever was typed while that
        // request was in flight. Pagination/sort links carry the same filters
        // the form already holds, so nothing needs syncing back.
        function navigate(url) {
            spinner.classList.remove('hidden');
            spinner.classList.add('flex');
            if (currentRequest) currentRequest.abort();
            const controller = new AbortController();
            currentRequest = controller;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, signal: controller.signal })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    results.innerHTML = data.table_html;
                    applyCounts(data.counts);
                    window.history.replaceState({}, '', url);
                })
                .catch(function (err) {
                    // Fall back to a normal page load rather than leaving a stale table.
                    if (err.name !== 'AbortError') window.location.href = url;
                })
                .finally(function () {
                    if (currentRequest === controller) {
                        spinner.classList.add('hidden');
                        spinner.classList.remove('flex');
                    }
                });
        }

        function refresh() {
            const params = new URLSearchParams(new FormData(form));
            if (!(params.get(searchParam) || '').trim()) params.delete(searchParam);
            const qs = params.toString();
            navigate(form.getAttribute('action') + (qs ? '?' + qs : ''));
        }

        form.addEventListener('change', function (e) {
            if (e.target.matches('[data-live-filter]')) {
                renderAppliedFilters();
                refresh();
            }
        });

        searchInput.addEventListener('input', function () {
            renderAppliedFilters();
            // Drop any in-flight search for older text so stale results never land.
            if (currentRequest) currentRequest.abort();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refresh, 400);
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            refresh();
        });

        form.addEventListener('click', function (e) {
            const clearGroup = e.target.closest('[data-clear-group]');
            const remove = e.target.closest('[data-remove-filter]');
            const clearAll = e.target.closest('[data-clear-all]');
            if (!clearGroup && !remove && !clearAll) return;

            if (clearAll) {
                form.querySelectorAll('input[data-live-filter]').forEach(function (cb) { cb.checked = false; });
                searchInput.value = '';
            } else if (clearGroup) {
                form.querySelectorAll('input[name="' + clearGroup.dataset.clearGroup + '[]"]').forEach(function (cb) { cb.checked = false; });
            } else if (remove.dataset.removeFilter === 'search') {
                searchInput.value = '';
            } else {
                form.querySelectorAll('input[name="' + remove.dataset.removeFilter + '[]"]').forEach(function (cb) {
                    if (cb.value === remove.dataset.value) cb.checked = false;
                });
            }

            clearTimeout(debounceTimer);
            renderAppliedFilters();
            refresh();
        });

        results.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-ajax-link], [data-ajax-link] a');
            if (!link || e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            navigate(link.href);
            results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        renderAppliedFilters();
    }

    document.querySelectorAll('form[data-live-filter-form]').forEach(initLiveFilter);
})();
</script>
@endpush
@endonce
