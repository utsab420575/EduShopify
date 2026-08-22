@extends('backend.layouts.supplier')

@section('title', 'Quotation — ' . $quotation->quotation_number)
@section('breadcrumb', 'Quotations / ' . $quotation->quotation_number)

@section('body')

    <x-backend.page-header title="Quotation {{ $quotation->quotation_number }}" subtitle="For RFQ: {{ $quotation->rfq?->title ?? 'RFQ #' . $quotation->rfq_id }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($quotation->status === 'revision_requested')
                    <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-rotate"></i> Submit Revision
                    </a>
                @elseif($quotation->status === 'draft')
                    <a href="{{ route('supplier.quotations.edit', $quotation) }}" class="btn-primary text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                @endif
                @if(in_array($quotation->status, ['submitted', 'under_review', 'revision_requested']))
                    <form method="POST" action="{{ route('supplier.quotations.withdraw', $quotation) }}" onsubmit="return confirm('Withdraw this quotation? You will no longer be considered for award.')">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                            Withdraw Quote
                        </button>
                    </form>
                @endif
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Revision Banner if requested --}}
    @if($quotation->status === 'revision_requested')
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> Buyer Requested a Quotation Revision
                </h4>
                <p class="text-xs text-amber-800 mt-1">{{ $quotation->revisionRequests->first()?->requested_changes ?? 'Please review your pricing or terms and submit a revised quote.' }}</p>
            </div>
            <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg shrink-0">
                Revise Quotation
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Left / Items & Commercials --}}
        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Quotation Overview">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$quotation->status" />
                    </div>
                    <div>
                        <span class="text-gray-400 block">Revision No.</span>
                        <span class="font-bold text-gray-800">#{{ $quotation->current_revision_no }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Total Quoted</span>
                        <span class="font-bold text-indigo-700 text-sm">{{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Delivery Lead Time</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : 'As requested' }}</span>
                    </div>
                </div>

                @if($quotation->proposal)
                    <div class="text-xs text-gray-700 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-100 mb-4">
                        <span class="font-bold block mb-1 text-gray-900">Proposal Summary:</span>
                        {{ $quotation->proposal }}
                    </div>
                @endif

                {{-- Terms --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Warranty:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->warranty_terms ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Support:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->support_terms ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Payment:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->payment_terms ?? 'Standard' }}</span>
                    </div>
                </div>
            </x-backend.form-card>

            {{-- Items Quoted --}}
            <x-backend.form-card title="Quoted Items">
                <div class="overflow-x-auto -mx-5 -mb-5">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-y border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Item</th>
                                <th class="px-3 py-3">Quantity</th>
                                <th class="px-3 py-3">Unit Price</th>
                                <th class="px-5 py-3 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($quotation->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                                        @if($item->is_alternative)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-800">Alternative</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs font-semibold text-gray-800">
                                        {{ (float)$item->quantity }}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-800">
                                        {{ $quotation->currency_code }} {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-xs font-bold text-gray-900 text-right">
                                        {{ $quotation->currency_code }} {{ number_format($item->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200 bg-gray-50 text-xs">
                            <tr>
                                <th colspan="3" class="px-5 py-3 text-right font-bold text-gray-700">Grand Total:</th>
                                <th class="px-5 py-3 text-right font-bold text-indigo-700 text-sm">
                                    {{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-backend.form-card>

            {{-- Revision History --}}
            @if($quotation->revisions->isNotEmpty())
                <x-backend.form-card title="Revision History">
                    <div class="space-y-3">
                        @foreach($quotation->revisions as $rev)
                            <div class="p-3 bg-gray-50 rounded-lg text-xs flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-900">Revision #{{ $rev->revision_no }} &middot; {{ $rev->created_at->format('d M Y, h:i A') }}</p>
                                    @if($rev->change_summary)
                                        <p class="text-gray-600 mt-0.5">{{ $rev->change_summary }}</p>
                                    @endif
                                </div>
                                <span class="font-bold text-gray-800">{{ $rev->currency_code }} {{ number_format($rev->grand_total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

        </div>

        {{-- Right / Buyer & RFQ Card --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Buyer Details">
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-gray-900 text-sm">{{ $quotation->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quotation->rfq?->buyerAccount?->display_name }}</p>
                    <p class="text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i>{{ $quotation->rfq?->buyerAccount?->buyerProfile?->country?->name ?? 'Location specified in RFQ' }}</p>
                    <div class="pt-3 border-t border-gray-100">
                        <a href="{{ route('supplier.opportunities.show', $quotation->rfq) }}" class="text-indigo-600 font-semibold hover:underline">
                            View Original RFQ &rarr;
                        </a>
                    </div>
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
