@extends('backend.layouts.buyer')

@section('title', 'Compare Quotations')
@section('breadcrumb', 'Procurement / RFQs / Compare')

@php
    $totalItems = $rfq->items->count();
@endphp

@section('body')

    <x-backend.page-header title="Compare Quotations" :subtitle="$rfq->title">
        <x-slot:actions>
            <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back to RFQ</a>
        </x-slot:actions>
    </x-backend.page-header>

    @if($rfq->quotations->isEmpty())
        <x-backend.empty-state icon="fa-scale-balanced" title="No quotations to compare" description="Once suppliers submit quotations for this RFQ, you can compare them here." />
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">Supplier</th>
                            @foreach($rfq->quotations as $quotation)
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ $quotation->supplierAccount?->supplierProfile?->display_name }}
                                    @if($quotation->shortlists->isNotEmpty())
                                        <span class="ml-1 inline-flex items-center gap-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-star"></i> Shortlisted</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Items Quoted</td>
                            @foreach($rfq->quotations as $quotation)
                                @php($quoted = $quotation->items->pluck('rfq_item_id')->filter()->unique()->count())
                                <td class="px-5 py-3.5 text-sm">
                                    {{ $quoted }} / {{ $totalItems }}
                                    @if($quoted < $totalItems)
                                        <span class="text-amber-600 text-xs">(Partial)</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Grand Total</td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Lead Time</td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Valid Until</td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Warranty</td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ Str::limit($quotation->warranty_terms, 60) ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-700 sticky left-0 bg-white">Status</td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5"><x-backend.status-badge :status="$quotation->status" /></td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-5 py-3.5 sticky left-0 bg-white"></td>
                            @foreach($rfq->quotations as $quotation)
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('buyer.quotations.show', $quotation) }}" class="text-sm font-medium" style="color:var(--theme-primary)">View full quote &rarr;</a>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
