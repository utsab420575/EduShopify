@extends('backend.layouts.supplier')

@section('title', 'RFQ Opportunities')
@section('breadcrumb', 'RFQ Opportunities / Available RFQs')

@section('body')

    <x-backend.page-header title="RFQ Opportunities" subtitle="Discover procurement requests from educational institutions seeking quotations." />

    {{-- RFQ source tabs — how the opportunity reached this supplier. Source-
         only: never mixed with the Status/Activity dropdown filters below. --}}
    <div class="bg-white rounded-xl border border-gray-200 p-3 mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($filterOptions as $key => $label)
                @php
                    $tabParams = array_filter(['filter' => $key === 'all' ? null : $key, 'status' => $selectedStatuses, 'activity' => $selectedActivities, 'q' => $search ?: null]);
                @endphp
                <a href="{{ route('supplier.opportunities.index', $tabParams) }}"
                   class="text-xs font-semibold px-3 py-2 rounded-lg {{ $filter === $key ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Compact, LIVE filter bar — every change here (search, checkboxes)
         re-fetches the table via AJAX immediately; nothing to "Apply". --}}
    <form id="opportunities-filter-form" method="GET" action="{{ route('supplier.opportunities.index') }}" class="bg-white rounded-xl border border-gray-200 p-3.5 mb-4 shadow-xs">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="hidden" name="sort" value="{{ $sort }}">

        {{-- Top Filter Row --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input (Larger & Flexible) --}}
            <div class="relative flex-1 min-w-[260px] sm:min-w-[320px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="Search RFQ title, RFQ #, or requirement..." autocomplete="off"
                       class="w-full text-xs pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white placeholder-gray-400">
            </div>

            {{-- RFQ Status dropdown (Reduced compact size) --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-44 shrink-0">
                <button type="button" @click="open = !open"
                        class="w-full text-xs font-semibold px-3 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-between gap-1.5 transition">
                    <span class="flex items-center gap-1.5 truncate">
                        <span>RFQ Status</span>
                        <span id="status-badge" class="{{ empty($selectedStatuses) ? 'hidden' : '' }} bg-indigo-600 text-white rounded-full text-[10px] px-1.5 font-bold leading-tight">{{ count($selectedStatuses) }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute z-20 mt-2 w-60 bg-white border border-gray-200 rounded-lg shadow-lg p-3 left-0">
                    <div class="space-y-1.5 max-h-64 overflow-y-auto">
                        @foreach($statusOptions as $key => $label)
                            <label class="flex items-center justify-between gap-2 text-xs text-gray-700 cursor-pointer hover:bg-gray-50 p-1 rounded transition">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="status[]" value="{{ $key }}" data-live-filter
                                           {{ in_array($key, $selectedStatuses) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $label }}
                                </span>
                                <span class="text-gray-400 text-[11px]" data-count="status.{{ $key }}">{{ $statusCounts[$key] ?? 0 }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                        <button type="button" data-clear-group="status" class="text-[11px] font-semibold text-gray-500 hover:text-red-600">Clear</button>
                        <span class="text-[11px] text-gray-400">Updates instantly</span>
                    </div>
                </div>
            </div>

            {{-- Supplier Activity dropdown (Reduced compact size) --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-48 shrink-0">
                <button type="button" @click="open = !open"
                        class="w-full text-xs font-semibold px-3 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-between gap-1.5 transition">
                    <span class="flex items-center gap-1.5 truncate">
                        <span>Activity</span>
                        <span id="activity-badge" class="{{ empty($selectedActivities) ? 'hidden' : '' }} bg-indigo-600 text-white rounded-full text-[10px] px-1.5 font-bold leading-tight">{{ count($selectedActivities) }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute z-20 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 right-0 sm:left-auto">
                    <div class="space-y-1.5 max-h-64 overflow-y-auto">
                        @foreach($activityOptions as $key => $label)
                            <label class="flex items-center justify-between gap-2 text-xs text-gray-700 cursor-pointer hover:bg-gray-50 p-1 rounded transition">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="activity[]" value="{{ $key }}" data-live-filter
                                           {{ in_array($key, $selectedActivities) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $label }}
                                </span>
                                <span class="text-gray-400 text-[11px]" data-count="activity.{{ $key }}">{{ $activityCounts[$key] ?? 0 }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                        <button type="button" data-clear-group="activity" class="text-[11px] font-semibold text-gray-500 hover:text-red-600">Clear</button>
                        <span class="text-[11px] text-gray-400">Updates instantly</span>
                    </div>
                </div>
            </div>

            {{-- Top Clear Filters Button --}}
            <button type="button" id="opportunities-reset-btn"
                    class="{{ (!empty($selectedStatuses) || !empty($selectedActivities) || $search !== '') ? '' : 'hidden' }} text-xs font-semibold px-3 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 flex items-center gap-1.5 transition shrink-0">
                <i class="fa-solid fa-rotate-left text-[11px]"></i> Clear Filters
            </button>

            {{-- Loading Spinner --}}
            <span id="opportunities-spinner" class="hidden text-xs text-indigo-600 flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0 font-medium">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Updating…
            </span>
        </div>

        {{-- Applied Filters Indicator Section --}}
        <div id="active-filters-container" class="{{ (!empty($selectedStatuses) || !empty($selectedActivities) || $search !== '') ? '' : 'hidden' }} pt-3 mt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-medium text-gray-500 mr-1 flex items-center gap-1">
                    <i class="fa-solid fa-filter text-[10px] text-gray-400"></i> Applied:
                </span>
                <div id="active-filters-pills" class="flex flex-wrap items-center gap-1.5">
                    @if($search !== '')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                            <span>Search: <strong>"{{ $search }}"</strong></span>
                            <button type="button" class="hover:text-red-600 text-indigo-400 cursor-pointer transition" data-remove-filter="q" title="Remove search">
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                            </button>
                        </span>
                    @endif
                    @foreach($selectedStatuses as $st)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                            <span>Status: <strong>{{ $statusOptions[$st] ?? $st }}</strong></span>
                            <button type="button" class="hover:text-red-600 text-emerald-500 cursor-pointer transition" data-remove-filter="status" data-value="{{ $st }}" title="Remove status filter">
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                            </button>
                        </span>
                    @endforeach
                    @foreach($selectedActivities as $act)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                            <span>Activity: <strong>{{ $activityOptions[$act] ?? $act }}</strong></span>
                            <button type="button" class="hover:text-red-600 text-blue-500 cursor-pointer transition" data-remove-filter="activity" data-value="{{ $act }}" title="Remove activity filter">
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
            <button type="button" id="active-filters-clear-all" class="text-xs font-semibold text-red-600 hover:text-red-800 hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-trash-can text-[11px]"></i> Clear all
            </button>
        </div>
    </form>

    {{-- Opportunities Table — swapped in place by AJAX for live filtering,
         sorting and pagination; see the script block below. --}}
    <div id="opportunities-results">
        @include('backend.supplier.procurement.opportunities.partials._table')
    </div>

    <script>
    (function () {
        const form = document.getElementById('opportunities-filter-form');
        const results = document.getElementById('opportunities-results');
        const spinner = document.getElementById('opportunities-spinner');
        const searchInput = form.querySelector('input[name="q"]');
        const statusLabels = @json($statusOptions);
        const activityLabels = @json($activityOptions);

        let debounceTimer = null;
        let currentRequest = null;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderAppliedFilters() {
            const pillsContainer = document.getElementById('active-filters-pills');
            const activeContainer = document.getElementById('active-filters-container');
            const topResetBtn = document.getElementById('opportunities-reset-btn');
            const statusBadge = document.getElementById('status-badge');
            const activityBadge = document.getElementById('activity-badge');

            const checkedStatuses = Array.from(form.querySelectorAll('input[name="status[]"]:checked')).map(cb => cb.value);
            const checkedActivities = Array.from(form.querySelectorAll('input[name="activity[]"]:checked')).map(cb => cb.value);
            const query = searchInput.value.trim();

            if (statusBadge) {
                statusBadge.textContent = checkedStatuses.length;
                statusBadge.classList.toggle('hidden', checkedStatuses.length === 0);
            }
            if (activityBadge) {
                activityBadge.textContent = checkedActivities.length;
                activityBadge.classList.toggle('hidden', checkedActivities.length === 0);
            }

            const hasActiveFilters = checkedStatuses.length > 0 || checkedActivities.length > 0 || query !== '';

            if (topResetBtn) {
                topResetBtn.classList.toggle('hidden', !hasActiveFilters);
            }
            if (activeContainer) {
                activeContainer.classList.toggle('hidden', !hasActiveFilters);
            }

            if (!pillsContainer) return;
            pillsContainer.innerHTML = '';

            // Search pill
            if (query !== '') {
                const pill = document.createElement('span');
                pill.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs';
                pill.innerHTML = `<span>Search: <strong>"${escapeHtml(query)}"</strong></span>
                    <button type="button" class="hover:text-red-600 text-indigo-400 ml-0.5 cursor-pointer transition" data-remove-filter="q" title="Remove search">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                    </button>`;
                pillsContainer.appendChild(pill);
            }

            // Status pills
            checkedStatuses.forEach(val => {
                const label = statusLabels[val] || val;
                const pill = document.createElement('span');
                pill.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs';
                pill.innerHTML = `<span>Status: <strong>${escapeHtml(label)}</strong></span>
                    <button type="button" class="hover:text-red-600 text-emerald-500 ml-0.5 cursor-pointer transition" data-remove-filter="status" data-value="${escapeHtml(val)}" title="Remove status filter">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                    </button>`;
                pillsContainer.appendChild(pill);
            });

            // Activity pills
            checkedActivities.forEach(val => {
                const label = activityLabels[val] || val;
                const pill = document.createElement('span');
                pill.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs';
                pill.innerHTML = `<span>Activity: <strong>${escapeHtml(label)}</strong></span>
                    <button type="button" class="hover:text-red-600 text-blue-500 ml-0.5 cursor-pointer transition" data-remove-filter="activity" data-value="${escapeHtml(val)}" title="Remove activity filter">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                    </button>`;
                pillsContainer.appendChild(pill);
            });
        }

        function clearAllFilters() {
            form.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = false; });
            searchInput.value = '';
            renderAppliedFilters();
            refresh();
        }

        function applyCounts(data) {
            document.querySelectorAll('[data-count]').forEach(function (el) {
                const [group, bucket] = el.getAttribute('data-count').split('.');
                const source = group === 'status' ? data.status_counts : data.activity_counts;
                el.textContent = (bucket === 'all' ? data.total_count : source?.[bucket]) ?? 0;
            });
        }

        function navigate(url, { pushHistory = true } = {}) {
            spinner.classList.remove('hidden');
            if (currentRequest) currentRequest.abort();
            const controller = new AbortController();
            currentRequest = controller;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    results.innerHTML = data.table_html;
                    applyCounts(data);
                    syncFormFromUrl(url);
                    if (pushHistory) window.history.replaceState({}, '', url);
                })
                .catch(function (err) { if (err.name !== 'AbortError') console.error(err); })
                .finally(function () { spinner.classList.add('hidden'); });
        }

        function syncFormFromUrl(url) {
            const params = new URL(url, window.location.origin).searchParams;
            form.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = false; });
            params.getAll('status[]').forEach(function (v) {
                const el = form.querySelector('input[name="status[]"][value="' + v + '"]');
                if (el) el.checked = true;
            });
            params.getAll('activity[]').forEach(function (v) {
                const el = form.querySelector('input[name="activity[]"][value="' + v + '"]');
                if (el) el.checked = true;
            });
            searchInput.value = params.get('q') || '';
            form.querySelector('input[name="sort"]').value = params.get('sort') || 'published_desc';
            renderAppliedFilters();
        }

        function buildUrlFromForm() {
            const params = new URLSearchParams(new FormData(form));
            if (!params.get('q')) params.delete('q');
            return window.location.pathname + '?' + params.toString();
        }

        function refresh() {
            navigate(buildUrlFromForm());
        }

        // Checkboxes: filter immediately and update applied pills
        form.addEventListener('change', function (e) {
            if (e.target.matches('[data-live-filter]')) {
                renderAppliedFilters();
                refresh();
            }
        });

        // Search: debounced live filter as you type
        searchInput.addEventListener('input', function () {
            renderAppliedFilters();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refresh, 400);
        });

        // Enter key (or any other implicit submit) also just live-filters
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            renderAppliedFilters();
            refresh();
        });

        // Per-dropdown "Clear"
        form.querySelectorAll('[data-clear-group]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const group = btn.getAttribute('data-clear-group');
                form.querySelectorAll('input[name="' + group + '[]"]').forEach(function (cb) { cb.checked = false; });
                renderAppliedFilters();
                refresh();
            });
        });

        // Individual pill remove button
        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('[data-remove-filter]');
            if (!removeBtn) return;
            const filterType = removeBtn.getAttribute('data-remove-filter');
            const filterVal = removeBtn.getAttribute('data-value');

            if (filterType === 'q') {
                searchInput.value = '';
            } else if (filterType === 'status') {
                const cb = form.querySelector('input[name="status[]"][value="' + filterVal + '"]');
                if (cb) cb.checked = false;
            } else if (filterType === 'activity') {
                const cb = form.querySelector('input[name="activity[]"][value="' + filterVal + '"]');
                if (cb) cb.checked = false;
            }
            renderAppliedFilters();
            refresh();
        });

        // Global Clear All Filters buttons
        const topResetBtn = document.getElementById('opportunities-reset-btn');
        if (topResetBtn) {
            topResetBtn.addEventListener('click', clearAllFilters);
        }

        const clearAllBtn = document.getElementById('active-filters-clear-all');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAllFilters);
        }

        // Pagination and sort links AJAX delegation
        results.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-ajax-link], [data-ajax-link] a');
            if (!link) return;
            e.preventDefault();
            navigate(link.href);
        });

        // Initial render on page load
        renderAppliedFilters();
    })();
    </script>

@endsection
