{{-- Self-contained: rendered both inline (normal page load) and standalone
     (AJAX live-filter/sort/pagination responses) — mirrors
     buyer/procurement/rfqs/partials/_table.blade.php's pattern. --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($quotations->isEmpty())
        <div class="p-8">
            <x-backend.empty-state icon="fa-inbox" title="No quotations found" description="There are no quotations matching the selected filters." />
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider">SL</th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider">Supplier</th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider">RFQ</th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'quotation_number', 'direction' => request('sort') === 'quotation_number' && request('direction') === 'asc' ? 'desc' : 'asc', 'page' => null]) }}"
                               data-ajax-link class="inline-flex items-center gap-1 hover:text-indigo-600">
                                Quote # <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider text-right">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'grand_total', 'direction' => request('sort') === 'grand_total' && request('direction') === 'asc' ? 'desc' : 'asc', 'page' => null]) }}"
                               data-ajax-link class="inline-flex items-center gap-1 justify-end hover:text-indigo-600 w-full">
                                Total <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 font-semibold uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($quotations as $quotation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-sm text-gray-500">{{ $quotations->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-900">{{ $quotation->supplierAccount?->supplierProfile?->display_name }}</p>
                                <p class="text-xs text-gray-400">{{ $quotation->submitted_at?->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->rfq->title }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->quotation_number }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</td>
                            <td class="px-5 py-3.5"><x-backend.status-badge :status="$quotation->status" /></td>
                            <td class="px-5 py-3.5">
                                @php
                                    // Same per-status $actions array driving both the
                                    // desktop pill row and the mobile dropdown — see
                                    // supplier/procurement/quotations/index.blade.php
                                    // and buyer/procurement/rfqs/partials/_table.blade.php
                                    // for the pattern this mirrors.
                                    $isShortlisted = $quotation->shortlists->isNotEmpty();
                                    $pendingAward = $quotation->award && $quotation->award->isAwaitingResponse();

                                    $actions = [
                                        ['label' => 'View', 'icon' => 'fa-regular fa-eye', 'href' => route('buyer.quotations.show', $quotation)],
                                    ];

                                    if (in_array($quotation->status, ['submitted', 'under_review', 'shortlisted', 'revision_requested', 'revised'], true)) {
                                        $actions[] = ['label' => 'Statistics', 'icon' => 'fa-solid fa-chart-column', 'stats' => true];
                                    }

                                    if ($quotation->status === 'submitted') {
                                        if ($pendingAward) {
                                            $actions[] = [
                                                'label' => 'Undo Award', 'icon' => 'fa-solid fa-rotate-left', 'danger' => true,
                                                'href' => route('buyer.quotations.award.cancel', $quotation), 'method' => 'DELETE',
                                                'confirm' => ['title' => 'Undo this award?', 'text' => 'The supplier will be notified and the RFQ reopens so you can award someone else.', 'icon' => 'question', 'confirmText' => 'Yes, undo award'],
                                            ];
                                        } else {
                                            $actions[] = [
                                                'label' => 'Award', 'icon' => 'fa-solid fa-trophy',
                                                'href' => route('buyer.quotations.award', $quotation), 'method' => 'POST',
                                                'confirm' => ['title' => 'Award this quotation?', 'text' => 'The supplier will have a limited time to accept or reject. Once accepted, a Purchase Order is created automatically.', 'icon' => 'question', 'confirmText' => 'Yes, award it'],
                                            ];
                                        }

                                        if ($isShortlisted) {
                                            $actions[] = ['label' => 'Unshortlist', 'icon' => 'fa-regular fa-star', 'href' => route('buyer.quotations.unshortlist', $quotation), 'method' => 'DELETE'];
                                        } else {
                                            $actions[] = ['label' => 'Shortlist', 'icon' => 'fa-solid fa-star', 'href' => route('buyer.quotations.shortlist', $quotation), 'method' => 'POST'];
                                        }
                                    }
                                @endphp

                                {{-- Desktop / tablet: icon + text pill buttons --}}
                                <div class="hidden md:flex items-center flex-wrap justify-end gap-1.5">
                                    @foreach($actions as $action)
                                        @include('backend.buyer.procurement.quotations.partials._action-button', ['action' => $action, 'quotation' => $quotation, 'variant' => 'pill'])
                                    @endforeach
                                    @if(in_array($quotation->status, $compareEligibleStatuses, true))
                                        <button type="button" title="Add to Compare"
                                                x-data="compareCheckbox({{ $quotation->rfq_id }}, {{ $quotation->id }})"
                                                data-max-items="{{ $maxCompareItems }}"
                                                @click="toggle"
                                                :class="checked ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' : 'text-gray-600 border-gray-300 hover:bg-gray-50'"
                                                class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border flex items-center gap-1.5 transition-colors whitespace-nowrap shrink-0">
                                            <i class="fa-solid fa-scale-balanced"></i> Compare
                                        </button>
                                    @endif
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
                                            @include('backend.buyer.procurement.quotations.partials._action-button', ['action' => $action, 'quotation' => $quotation, 'variant' => 'menu'])
                                        @endforeach
                                        @if(in_array($quotation->status, $compareEligibleStatuses, true))
                                            <button type="button"
                                                    x-data="compareCheckbox({{ $quotation->rfq_id }}, {{ $quotation->id }})"
                                                    data-max-items="{{ $maxCompareItems }}"
                                                    @click="toggle"
                                                    :class="checked ? 'text-emerald-600' : 'text-gray-700'"
                                                    class="w-full text-left text-xs px-3 py-2 flex items-center gap-2.5 hover:bg-gray-50">
                                                <i class="fa-solid fa-scale-balanced w-3.5 text-center"></i> <span x-text="checked ? 'Remove from Compare' : 'Add to Compare'"></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($quotations->total() > 0)
        <div class="p-4 border-t border-gray-100" data-ajax-link>
            <x-backend.pagination :paginator="$quotations" />
        </div>
    @endif
</div>
