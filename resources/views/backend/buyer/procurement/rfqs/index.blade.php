@extends('backend.layouts.buyer')

@section('title', 'My RFQs')
@section('breadcrumb', 'Procurement / RFQs')

@section('body')

<div x-data="rfqStatisticsModal()">

    <x-backend.page-header title="My RFQs" subtitle="Manage your requests for quotation.">
        <x-slot:actions>
            @can('create', \App\Models\Rfq::class)
                <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Create RFQ
                </a>
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Visibility-type tabs — how the RFQ was targeted at suppliers.
         Tab-only: never mixed with the Status dropdown filter below. --}}
    <div class="bg-white rounded-xl border border-gray-200 p-3 mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($filterOptions as $key => $label)
                @php
                    $tabParams = array_filter(['filter' => $key === 'all' ? null : $key, 'status' => $selectedStatuses, 'q' => $search ?: null]);
                @endphp
                <a href="{{ route('buyer.rfqs.index', $tabParams) }}"
                   class="text-xs font-semibold px-3 py-2 rounded-lg {{ $filter === $key ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Compact, LIVE filter bar — every change here (search, checkboxes)
         re-fetches the table via AJAX immediately; nothing to "Apply". --}}
    <form id="rfqs-filter-form" method="GET" action="{{ route('buyer.rfqs.index') }}" class="bg-white rounded-xl border border-gray-200 p-3.5 mb-4 shadow-xs">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="hidden" name="sort" value="{{ $sort }}">

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[260px] sm:min-w-[320px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="Search RFQ number or title..." autocomplete="off"
                       class="w-full text-xs pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white placeholder-gray-400">
            </div>

            {{-- RFQ Status dropdown --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-60 shrink-0">
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

            {{-- Loading Spinner --}}
            <span id="rfqs-spinner" class="hidden text-xs text-indigo-600 flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0 font-medium">
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
                </div>
            </div>
            <button type="button" id="active-filters-clear-all" class="text-xs font-semibold text-red-600 hover:text-red-800 hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-trash-can text-[11px]"></i> Clear all
            </button>
        </div>
    </form>

    {{-- RFQ Table — swapped in place by AJAX for live filtering, sorting
         and pagination; see the script block below. --}}
    <div id="rfqs-results">
        @include('backend.buyer.procurement.rfqs.partials._table')
    </div>

    {{-- Shared "Statistics" modal — one instance reused for whichever row's
         button was clicked, populated via openStatistics(rfqId) below. --}}
    <x-backend.modal id="rfq-statistics" width="max-w-xl">
        <div x-show="statsLoading" class="py-10 text-center text-sm text-gray-400">
            <i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Loading…
        </div>
        <template x-if="!statsLoading && stats">
            <div>
                <div class="flex items-start justify-between gap-3 -mt-1">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wide">RFQ Statistics</p>
                        <p class="text-base font-bold text-gray-900 truncate" x-text="stats.rfq_title"></p>
                    </div>
                    <button type="button" @click="open = false" class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-4">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-paper-plane text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Suppliers Notified</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.total_notified"></dd>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="w-7 h-7 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-eye text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Suppliers Viewed</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.viewed"></dd>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-hand-point-up text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Suppliers Interested</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.interested"></dd>
                    </div>
                    <div class="rounded-xl border border-purple-100 bg-purple-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-comment-dots text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Messages Received</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.messaged"></dd>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-file-invoice-dollar text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Quotations Received</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.quotations_received"></dd>
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-rose-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mb-1.5"><i class="fa-regular fa-clock text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Days Remaining</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.days_remaining ?? '—'"></dd>
                    </div>
                </dl>

                <div class="flex items-center gap-1.5 mt-4 text-xs text-gray-400">
                    <i class="fa-regular fa-clock text-[11px]"></i>
                    <span>Last activity: <span class="text-gray-600 font-medium" x-text="stats.last_activity_human || 'No activity yet'"></span></span>
                </div>

                <div class="flex items-center justify-end gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
                    <a :href="'{{ url('/buyer/rfqs') }}/' + stats.rfq_id + '?_tab=statistics'"
                       class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                        View Full Statistics <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </template>
        <template x-if="!statsLoading && !stats">
            <p class="py-10 text-center text-sm text-gray-400">Could not load statistics.</p>
        </template>
    </x-backend.modal>

</div>

<script>
(function () {
    const form = document.getElementById('rfqs-filter-form');
    const results = document.getElementById('rfqs-results');
    const spinner = document.getElementById('rfqs-spinner');
    const searchInput = form.querySelector('input[name="q"]');
    const statusLabels = @json($statusOptions);

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
        const statusBadge = document.getElementById('status-badge');

        const checkedStatuses = Array.from(form.querySelectorAll('input[name="status[]"]:checked')).map(cb => cb.value);
        const query = searchInput.value.trim();

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
                <button type="button" class="hover:text-red-600 text-indigo-400 ml-0.5 cursor-pointer transition" data-remove-filter="q" title="Remove search">
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
        searchInput.value = params.get('q') || '';
        form.querySelector('input[name="sort"]').value = params.get('sort') || 'created_desc';
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

    form.addEventListener('change', function (e) {
        if (e.target.matches('[data-live-filter]')) {
            renderAppliedFilters();
            refresh();
        }
    });

    searchInput.addEventListener('input', function () {
        renderAppliedFilters();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refresh, 400);
    });

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

        if (filterType === 'q') {
            searchInput.value = '';
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

@endsection

@push('scripts')
<script>
    function rfqStatisticsModal() {
        return {
            statsLoading: false,
            stats: null,

            openStatistics(rfqId) {
                this.statsLoading = true;
                this.stats = null;
                window.dispatchEvent(new CustomEvent('open-modal-rfq-statistics'));

                fetch('{{ url('/buyer/rfqs') }}/' + rfqId + '/statistics', {
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
@endpush
