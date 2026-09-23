@extends('backend.layouts.supplier')

@section('title', 'My Quotations')
@section('breadcrumb', 'Quotations / All Quotations')

@section('body')

<div x-data="quotationStatisticsModal()">

    <x-backend.page-header title="My Quotations" subtitle="Track quotation submissions, buyer reviews, shortlist status, and award decisions." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('supplier.quotations.index') }}" class="text-xs font-semibold px-3 py-2 rounded-lg {{ !$status ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </a>
            @foreach($statusOptions as $key => $label)
                <a href="{{ route('supplier.quotations.index', ['status' => $key]) }}" class="text-xs font-semibold px-3 py-2 rounded-lg {{ $status === $key ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Quotations Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($quotations->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-file-invoice" title="No quotations found" description="Browse available RFQ opportunities and submit your proposals." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Quotation / RFQ</th>
                            <th class="px-3 py-3.5 font-semibold">Buyer</th>
                            <th class="px-3 py-3.5 font-semibold">Total Amount</th>
                            <th class="px-3 py-3.5 font-semibold">Revision</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quotations as $quote)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supplier.quotations.show', $quote) }}" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs">
                                        {{ $quote->rfq?->title ?? 'RFQ #' . $quote->rfq_id }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $quote->quotation_number }} &middot; {{ $quote->submitted_at?->format('d M Y') ?? $quote->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    {{ $quote->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quote->rfq?->buyerAccount?->display_name }}
                                </td>
                                <td class="px-3 py-3.5 font-bold text-indigo-700 text-xs">
                                    {{ $quote->currency_code }} {{ number_format($quote->grand_total, 2) }}
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-500">
                                    {{ $quote->current_revision_no > 0 ? 'Rev #'.$quote->current_revision_no : 'Draft' }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$quote->status" />
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        // Same per-status $actions array driving both the
                                        // desktop pill row and the mobile dropdown — see
                                        // buyer/procurement/rfqs/partials/_table.blade.php
                                        // for the pattern this mirrors.
                                        $actions = [
                                            ['label' => 'View', 'icon' => 'fa-regular fa-eye', 'href' => route('supplier.quotations.show', $quote)],
                                        ];

                                        if ($quote->status === 'draft') {
                                            $actions[] = ['label' => 'Edit Draft', 'icon' => 'fa-solid fa-pen', 'href' => route('supplier.quotations.edit', $quote)];
                                        } else {
                                            $actions[] = ['label' => 'Statistics', 'icon' => 'fa-solid fa-chart-column', 'stats' => true];
                                            if (auth()->user()?->can('undoSubmit', $quote)) {
                                                $actions[] = [
                                                    'label' => 'Undo Submit', 'icon' => 'fa-solid fa-rotate-left', 'danger' => true,
                                                    'href' => route('supplier.quotations.undo-submit', $quote), 'method' => 'POST',
                                                    'confirm' => ['title' => 'Undo this submission?', 'text' => 'The quotation goes back to draft so you can make changes, then submit again when ready.', 'icon' => 'question', 'confirmText' => 'Yes, undo submit'],
                                                ];
                                            }
                                        }
                                    @endphp

                                    {{-- Desktop / tablet: icon + text pill buttons --}}
                                    <div class="hidden md:flex items-center flex-wrap justify-end gap-1.5">
                                        @foreach($actions as $action)
                                            @include('backend.supplier.procurement.quotations.partials._action-button', ['action' => $action, 'quotation' => $quote, 'variant' => 'pill'])
                                        @endforeach
                                    </div>

                                    {{-- Mobile: collapse into a "More" dropdown --}}
                                    <div class="md:hidden relative flex justify-end" x-data="{ menuOpen: false }" @click.away="menuOpen = false">
                                        <button type="button" @click="menuOpen = !menuOpen" title="More actions"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div x-show="menuOpen" x-cloak @click="menuOpen = false"
                                             x-transition
                                             class="absolute right-0 top-full mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                                            @foreach($actions as $action)
                                                @include('backend.supplier.procurement.quotations.partials._action-button', ['action' => $action, 'quotation' => $quote, 'variant' => 'menu'])
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($quotations->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $quotations->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Shared "Statistics" modal — one instance reused for whichever row's
         button was clicked, populated via openStatistics(quotationId) below.
         Mirrors buyer/procurement/rfqs/index.blade.php's rfq-statistics modal. --}}
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
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="w-7 h-7 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-eye text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Viewed by Buyer</dt>
                        <dd class="text-sm font-bold text-gray-900" x-text="stats.viewed_by_buyer ? stats.viewed_at_human : 'Not yet'"></dd>
                    </div>
                    <div class="rounded-xl border border-purple-100 bg-purple-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-comment-dots text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Messages</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.messages_count"></dd>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-1.5"><i class="fa-solid fa-rotate text-xs"></i></div>
                        <dt class="text-[10.5px] font-medium text-gray-500 leading-tight">Revision Requests</dt>
                        <dd class="text-lg font-bold text-gray-900" x-text="stats.revision_requests_count"></dd>
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
                    <a :href="'{{ url('/supplier/quotations') }}/' + stats.quotation_id + '?_tab=activity'"
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

                fetch('{{ url('/supplier/quotations') }}/' + quotationId + '/statistics', {
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
