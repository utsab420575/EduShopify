@extends('backend.layouts.supplier')

@section('title', 'Award ' . $award->award_number)
@section('breadcrumb', 'Awards / ' . $award->award_number)

@section('body')

    <x-backend.page-header title="Award {{ $award->award_number }}" subtitle="RFQ: {{ $award->rfq?->title ?? 'RFQ #' . $award->rfq_id }}" />

    @if($award->isAwaitingResponse())
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h4 class="text-base font-bold text-indigo-950 flex items-center gap-2">
                        <i class="fa-solid fa-trophy text-amber-500"></i> Congratulations! The buyer has chosen your quotation.
                    </h4>
                    <p class="text-xs text-indigo-800 mt-1">Please accept or reject this award before <strong>{{ $award->response_deadline?->format('d M Y, h:i A') ?? 'the deadline' }}</strong>. Accepting will instantly issue the Purchase Order.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <form method="POST" action="{{ route('supplier.awards.accept', $award) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-check"></i> Accept Award
                        </button>
                    </form>
                    <button @click="$dispatch('open-reject-modal')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                        Reject
                    </button>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div x-data="{ open: false }" @open-reject-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.outside="open = false">
                <h3 class="text-base font-bold text-gray-900 mb-2">Reject Award</h3>
                <p class="text-xs text-gray-500 mb-4">Please provide a reason for rejecting this award. The buyer will be notified.</p>
                <form method="POST" action="{{ route('supplier.awards.reject', $award) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Rejection Reason" required placeholder="e.g. Unable to meet the requested timeline due to unexpected stock constraints." />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-200 text-gray-600">Cancel</button>
                        <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Award Summary">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$award->status" />
                    </div>
                    <div>
                        <span class="text-gray-400 block">Awarded Date</span>
                        <span class="font-bold text-gray-800">{{ $award->awarded_at?->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Quotation Ref</span>
                        <span class="font-bold text-indigo-700">{{ $award->quotation?->quotation_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Award Total</span>
                        <span class="font-bold text-indigo-700 text-sm">{{ $award->quotation?->currency_code }} {{ number_format($award->quotation?->grand_total, 2) }}</span>
                    </div>
                </div>

                @if($award->award_note)
                    <div class="text-xs text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 mb-4">
                        <span class="font-bold block mb-1 text-gray-900">Buyer Note:</span>
                        {{ $award->award_note }}
                    </div>
                @endif

                @if($award->purchaseOrder)
                    <div class="p-4 bg-green-50 rounded-xl border border-green-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-green-800 font-medium">Purchase Order Issued</p>
                            <p class="text-sm font-bold text-green-950">{{ $award->purchaseOrder->po_number }}</p>
                        </div>
                        <a href="{{ route('supplier.purchase-orders.show', $award->purchaseOrder) }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            View Purchase Order &rarr;
                        </a>
                    </div>
                @endif
            </x-backend.form-card>

            {{-- Items from quotation --}}
            <x-backend.form-card title="Awarded Items">
                <div class="overflow-x-auto -mx-5 -mb-5">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-y border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Item</th>
                                <th class="px-3 py-3">Quantity</th>
                                <th class="px-3 py-3">Unit Price</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @foreach($award->quotation?->items ?? [] as $item)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-3 py-3">{{ (float)$item->quantity }}</td>
                                    <td class="px-3 py-3">{{ $award->quotation->currency_code }} {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 font-bold text-right text-gray-900">{{ $award->quotation->currency_code }} {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-backend.form-card>

        </div>

        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Buyer Information">
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-gray-900 text-sm">{{ $award->buyerAccount?->buyerProfile?->organization_name ?? $award->buyerAccount?->display_name }}</p>
                    <p class="text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i>{{ $award->buyerAccount?->buyerProfile?->country?->name ?? 'Buyer Country' }}</p>
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
