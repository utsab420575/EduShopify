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

    @if($compareRfqId)
        <p class="text-xs text-gray-500 -mt-4 mb-6">Check "Add to Compare" on 2–5 quotations below, then use "Compare Quotations" to view them side by side. Selections only apply to this RFQ.</p>
    @endif

    <x-backend.form-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-backend.select name="rfq" label="RFQ" placeholder="All RFQs">
                @foreach($rfqOptions as $option)
                    <option value="{{ $option->id }}" @selected($rfq === $option->id)>{{ $option->title }} ({{ $option->rfq_number }})</option>
                @endforeach
            </x-backend.select>
            <x-backend.select name="status" label="Status" :options="$statusOptions" placeholder="All Statuses" :selected="$status" />
            <div class="flex items-end">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg w-full">Filter</button>
            </div>
        </form>
    </x-backend.form-card>

    <x-backend.table>
        @if($quotations->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-inbox" title="No quotations found" description="There are no quotations matching the selected filters." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    @if($compareRfqId)
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compare</th>
                    @endif
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quote #</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($quotations as $quotation)
                <tr class="hover:bg-gray-50">
                    @if($compareRfqId)
                        <td class="px-5 py-3.5">
                            @if((int) $quotation->rfq_id === $compareRfqId && in_array($quotation->status, $compareEligibleStatuses, true))
                                <label class="inline-flex items-center gap-2 cursor-pointer" x-data="compareCheckbox({{ $compareRfqId }}, {{ $quotation->id }})" data-max-items="{{ $maxCompareItems }}">
                                    <input type="checkbox" x-model="checked" @change="toggle" class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                                    <span class="text-xs text-gray-500">Add to Compare</span>
                                </label>
                            @else
                                <span class="text-xs text-gray-300">&mdash;</span>
                            @endif
                        </td>
                    @endif
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $quotation->supplierAccount?->supplierProfile?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $quotation->submitted_at?->format('d M Y') }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->rfq->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->quotation_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$quotation->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.quotations.show', $quotation) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
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
    @endif

    @include('backend.buyer.procurement.quotations.partials._compare-store')

@endsection
