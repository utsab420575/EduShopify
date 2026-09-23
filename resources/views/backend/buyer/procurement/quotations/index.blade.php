@extends('backend.layouts.buyer')

@section('title', 'Received Quotations')
@section('breadcrumb', 'Procurement / Quotations')

@php
    $compareRfqId = $rfq ?: null;
@endphp

@section('body')

<div x-data="quotationStatisticsModal()">

    <x-backend.page-header title="Received Quotations" subtitle="Quotations submitted by suppliers against your RFQs.">
        @if($compareRfqId)
            <x-slot:actions>
                <a href="{{ route('buyer.quotations.compare', $compareRfqId) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" x-data="compareTray({{ $compareRfqId }}, {{ $maxCompareItems }})">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Compare Quotations <span x-show="count > 0" x-cloak x-text="'(' + count + ')'"></span>
                </a>
            </x-slot:actions>
        @endif
    </x-backend.page-header>

    <p class="text-xs text-gray-500 -mt-4 mb-6">
        Use the <i class="fa-solid fa-scale-balanced"></i> icon to add 2–5 quotations from the same RFQ to a comparison, then open it from
        <a href="{{ route('buyer.quotations.compare-index') }}" class="font-medium text-gray-700 hover:underline">Procurement &rsaquo; Compare</a>.
    </p>

    {{-- Compact, LIVE filter bar — every change here (search, RFQ, status
         checkboxes) re-fetches the table via AJAX immediately; nothing to
         "Apply". Mirrors buyer/procurement/rfqs/index.blade.php's bar. --}}
    <form id="quotations-filter-form" method="GET" action="{{ route('buyer.quotations.index') }}" class="bg-white rounded-xl border border-gray-200 p-3.5 mb-4 shadow-xs">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[260px] sm:min-w-[320px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search quote #, RFQ title, or supplier..." autocomplete="off"
                       class="w-full text-xs pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white placeholder-gray-400">
                <button type="button" id="clear-search-btn" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ $search ? '' : 'hidden' }}" title="Clear search">
                    <i class="fa-solid fa-circle-xmark text-xs"></i>
                </button>
            </div>

            {{-- Status dropdown (multi-select) --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-60 shrink-0">
                <button type="button" @click="open = !open"
                        class="w-full text-xs font-semibold px-3 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-between gap-1.5 transition">
                    <span class="flex items-center gap-1.5 truncate">
                        <span>Status</span>
                        <span id="status-badge" class="{{ empty($selectedStatuses) ? 'hidden' : '' }} bg-indigo-600 text-white rounded-full text-[10px] px-1.5 font-bold leading-tight">{{ count($selectedStatuses) }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute z-20 mt-2 w-60 bg-white border border-gray-200 rounded-lg shadow-lg p-3 right-0 sm:left-0">
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

            {{-- Loading Spinner --}}
            <span id="quotations-spinner" class="hidden text-xs text-indigo-600 flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0 font-medium">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Updating…
            </span>
        </div>

        {{-- Applied Filters Indicator --}}
        <div id="active-filters-container" class="{{ (!empty($selectedStatuses) || $search !== '') ? '' : 'hidden' }} pt-3 mt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-medium text-gray-500 mr-1 flex items-center gap-1">
                    <i class="fa-solid fa-filter text-[10px] text-gray-400"></i> Applied:
                </span>
                <div id="active-filters-pills" class="flex flex-wrap items-center gap-1.5">
                    @if($search !== '')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                            <span>Search: <strong>"{{ $search }}"</strong></span>
                            <button type="button" class="hover:text-red-600 text-indigo-400 cursor-pointer transition" data-remove-filter="search" title="Remove search">
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
                </div>
            </div>
            <button type="button" id="active-filters-clear-all" class="text-xs font-semibold text-red-600 hover:text-red-800 hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-trash-can text-[11px]"></i> Clear all
            </button>
        </div>
    </form>

    {{-- Quotations table — swapped in place by AJAX for live filtering,
         sorting and pagination; see the script block below. --}}
    <div id="quotations-results">
        @include('backend.buyer.procurement.quotations.partials._table')
    </div>

    @if(!empty($compareRfqId))
        <div
            x-data="compareTray({{ $compareRfqId }}, {{ $maxCompareItems }})"
            x-show="count > 0"
            x-cloak
            class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-600"><span class="font-semibold text-gray-900" x-text="count"></span> quotation<span x-show="count !== 1">s</span> selected for comparison (max {{ $maxCompareItems }}).</p>
                <a href="{{ route('buyer.quotations.compare', $compareRfqId) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Compare Quotations
                </a>
            </div>
        </div>
        <div class="h-16"></div>
    @else
        <div x-data="compareTrayGlobal()">
            <div
                x-show="totalCount > 0"
                x-cloak
                class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-600"><span class="font-semibold text-gray-900" x-text="totalCount"></span> quotation<span x-show="totalCount !== 1">s</span> selected across <span class="font-semibold text-gray-900" x-text="rfqCount"></span> RFQ<span x-show="rfqCount !== 1">s</span>.</p>
                    <a href="{{ route('buyer.quotations.compare-index') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                        <i class="fa-solid fa-scale-balanced mr-1"></i> View Comparisons
                    </a>
                </div>
            </div>
            <div x-show="totalCount > 0" x-cloak class="h-16"></div>
        </div>
    @endif

    @include('backend.buyer.procurement.quotations.partials._compare-store')

    {{-- Shared "Statistics" modal — one instance reused for whichever row's
         button was clicked, populated via openStatistics(quotationId) below.
         Mirrors supplier/procurement/quotations/index.blade.php's modal. --}}
    <x-backend.modal id="quotation-statistics" width="max-w-xl">
        <div x-show="statsLoading" class="py-10 text-center text-sm text-gray-400">
            <i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Loading…
        </div>
        <template x-if="!statsLoading && stats">
            <div>
                <div class="flex items-start justify-between gap-3 -mt-1">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wide">Quotation Statistics</p>
                        <p class="text-base font-bold text-gray-900 truncate" x-text="stats.quotation_number"></p>
                    </div>
                    <button type="button" @click="open = false" class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-4">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-paper-plane text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Submitted</dt>
                        <dd class="text-sm font-bold text-gray-900" x-text="stats.submitted_at_human || '—'"></dd>
                    </div>
                    <div class="rounded-xl border border-purple-100 bg-purple-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-comment-dots text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Messages</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.messages_count"></dd>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-code-branch text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Revision No.</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.revision_no > 0 ? '#' + stats.revision_no : '—'"></dd>
                    </div>
                </dl>

                <div class="flex items-center gap-1.5 mt-4 text-xs text-gray-400">
                    <i class="fa-regular fa-clock text-[11px]"></i>
                    <span>Last activity: <span class="text-gray-600 font-medium" x-text="stats.last_activity_label ? (stats.last_activity_label + ' — ' + stats.last_activity_human) : 'No activity yet'"></span></span>
                </div>

                <div class="flex items-center justify-end gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
                    <a :href="'{{ url('/buyer/quotations') }}/' + stats.quotation_id"
                       class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                        View Quotation <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </template>
        <template x-if="!statsLoading && !stats">
            <p class="py-10 text-center text-sm text-gray-400">Could not load statistics.</p>
        </template>
    </x-backend.modal>

</div>

@endsection

@push('scripts')
<script>
    function quotationStatisticsModal() {
        return {
            statsLoading: false,
            stats: null,

            openStatistics(quotationId) {
                this.statsLoading = true;
                this.stats = null;
                window.dispatchEvent(new CustomEvent('open-modal-quotation-statistics'));

                fetch('{{ url('/buyer/quotations') }}/' + quotationId + '/statistics', {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(data => { this.stats = data; })
                    .catch(() => { this.stats = null; })
                    .finally(() => { this.statsLoading = false; });
            },
        };
    }
</script>

<script>
(function () {
    const form = document.getElementById('quotations-filter-form');
    const results = document.getElementById('quotations-results');
    const spinner = document.getElementById('quotations-spinner');
    const searchInput = form.querySelector('input[name="search"]');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const statusLabels = @json($statusOptions);

    let debounceTimer = null;
    let currentRequest = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updateSearchClearBtn() {
        if (clearSearchBtn) {
            clearSearchBtn.classList.toggle('hidden', !searchInput.value.trim());
        }
    }

    function renderAppliedFilters() {
        const pillsContainer = document.getElementById('active-filters-pills');
        const activeContainer = document.getElementById('active-filters-container');
        const statusBadge = document.getElementById('status-badge');

        const checkedStatuses = Array.from(form.querySelectorAll('input[name="status[]"]:checked')).map(cb => cb.value);
        const query = searchInput.value.trim();

        updateSearchClearBtn();

        if (statusBadge) {
            statusBadge.textContent = checkedStatuses.length;
            statusBadge.classList.toggle('hidden', checkedStatuses.length === 0);
        }

        const hasActiveFilters = checkedStatuses.length > 0 || query !== '';

        if (activeContainer) activeContainer.classList.toggle('hidden', !hasActiveFilters);

        if (!pillsContainer) return;
        pillsContainer.innerHTML = '';

        if (query !== '') {
            const pill = document.createElement('span');
            pill.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs';
            pill.innerHTML = `<span>Search: <strong>"${escapeHtml(query)}"</strong></span>
                <button type="button" class="hover:text-red-600 text-indigo-400 ml-0.5 cursor-pointer transition" data-remove-filter="search" title="Remove search">
                    <i class="fa-solid fa-xmark text-[11px]"></i>
                </button>`;
            pillsContainer.appendChild(pill);
        }

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
    }

    function clearAllFilters() {
        form.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = false; });
        searchInput.value = '';
        updateSearchClearBtn();
        renderAppliedFilters();
        refresh();
    }

    function applyCounts(data) {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            const [group, bucket] = el.getAttribute('data-count').split('.');
            el.textContent = (group === 'status' ? data.status_counts?.[bucket] : 0) ?? 0;
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
        if (document.activeElement !== searchInput) {
            searchInput.value = params.get('search') || '';
        }
        form.querySelector('input[name="sort"]').value = params.get('sort') || '';
        form.querySelector('input[name="direction"]').value = params.get('direction') || '';
        updateSearchClearBtn();
        renderAppliedFilters();
    }

    function buildUrlFromForm() {
        const params = new URLSearchParams(new FormData(form));
        if (!params.get('search')) params.delete('search');
        if (!params.get('sort')) params.delete('sort');
        if (!params.get('direction')) params.delete('direction');
        // status[] must always be present (even empty) so an explicit
        // "clear all statuses" is distinguishable from "never touched" —
        // see QuotationController::index()'s $request->has('status') check.
        if (!params.has('status[]')) params.set('status', '');
        return window.location.pathname + '?' + params.toString();
    }

    function refresh() {
        navigate(buildUrlFromForm());
    }

    form.addEventListener('change', function (e) {
        if (e.target.matches('[data-live-filter]')) {
            renderAppliedFilters();
            refresh();
        }
    });

    searchInput.addEventListener('input', function () {
        updateSearchClearBtn();
        renderAppliedFilters();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refresh, 300);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            updateSearchClearBtn();
            renderAppliedFilters();
            refresh();
        }
    });

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            searchInput.value = '';
            updateSearchClearBtn();
            renderAppliedFilters();
            searchInput.focus();
            refresh();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        renderAppliedFilters();
        refresh();
    });

    form.querySelectorAll('[data-clear-group]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = btn.getAttribute('data-clear-group');
            form.querySelectorAll('input[name="' + group + '[]"]').forEach(function (cb) { cb.checked = false; });
            renderAppliedFilters();
            refresh();
        });
    });

    document.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('[data-remove-filter]');
        if (!removeBtn) return;
        const filterType = removeBtn.getAttribute('data-remove-filter');
        const filterVal = removeBtn.getAttribute('data-value');

        if (filterType === 'search') {
            searchInput.value = '';
            updateSearchClearBtn();
        } else if (filterType === 'status') {
            const cb = form.querySelector('input[name="status[]"][value="' + filterVal + '"]');
            if (cb) cb.checked = false;
        }
        renderAppliedFilters();
        refresh();
    });

    const clearAllBtn = document.getElementById('active-filters-clear-all');
    if (clearAllBtn) clearAllBtn.addEventListener('click', clearAllFilters);

    results.addEventListener('click', function (e) {
        const link = e.target.closest('a[data-ajax-link], [data-ajax-link] a');
        if (!link) return;
        e.preventDefault();
        navigate(link.href);
    });

    renderAppliedFilters();
})();
</script>
@endpush
