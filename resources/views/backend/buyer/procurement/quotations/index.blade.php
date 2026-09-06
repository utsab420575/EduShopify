@extends('backend.layouts.buyer')

@section('title', 'Received Quotations')
@section('breadcrumb', 'Procurement / Quotations')

@php
    $compareRfqId = $rfq ?: null;
@endphp

@section('body')

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

    <x-backend.table>
        <x-slot:toolbar>
            <x-backend.table-search
                title="Quotations" :count="$quotations->total()"
                search-param="search" page-param="page"
                :current-search="$search" placeholder="Search quote # or supplier..."
                :filter-params="['rfq', 'status']" :has-active-filter="$rfq || $status !== ''">
                <x-slot:filters>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">RFQ</label>
                        <select name="rfq" onchange="this.form.submit()" class="focus-accent w-52 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All RFQs</option>
                            @foreach($rfqOptions as $option)
                                <option value="{{ $option->id }}" @selected($rfq === $option->id)>{{ $option->title }} ({{ $option->rfq_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" onchange="this.form.submit()" class="focus-accent w-40 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Statuses</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($rfq || $status !== '')
                        <a href="{{ request()->fullUrlWithQuery(['rfq' => null, 'status' => null, 'page' => null]) }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                    @endif
                </x-slot:filters>
            </x-backend.table-search>
        </x-slot:toolbar>

        @if($quotations->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-inbox" title="No quotations found" description="There are no quotations matching the selected filters." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <x-backend.sortable-th column="quotation_number" label="Quote #" />
                    <x-backend.sortable-th column="grand_total" label="Total" align="right" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
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
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('buyer.quotations.show', $quotation) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                            @if(in_array($quotation->status, $compareEligibleStatuses, true))
                                <button type="button" title="Add to Compare"
                                        x-data="compareCheckbox({{ $quotation->rfq_id }}, {{ $quotation->id }})"
                                        data-max-items="{{ $maxCompareItems }}"
                                        @click="toggle"
                                        :class="checked ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' : 'text-gray-500 hover:bg-gray-100'"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-scale-balanced"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$quotations" />
        </x-slot:pagination>
    </x-backend.table>

    @if($compareRfqId)
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

@endsection
