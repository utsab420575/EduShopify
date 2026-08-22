@extends('backend.layouts.supplier')

@section('title', 'PO ' . $purchaseOrder->po_number)
@section('breadcrumb', 'Purchase Orders / ' . $purchaseOrder->po_number)

@section('body')

    <x-backend.page-header title="Purchase Order {{ $purchaseOrder->po_number }}" subtitle="Generated for {{ $purchaseOrder->buyerAccount?->buyerProfile?->organization_name ?? $purchaseOrder->buyerAccount?->display_name }}" />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Order Summary">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$purchaseOrder->status" />
                    </div>
                    <div>
                        <span class="text-gray-400 block">Issued Date</span>
                        <span class="font-bold text-gray-800">{{ $purchaseOrder->issued_at?->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">RFQ Reference</span>
                        <span class="font-semibold text-gray-800">{{ $purchaseOrder->rfq?->rfq_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Grand Total</span>
                        <span class="font-bold text-indigo-700 text-sm">{{ $purchaseOrder->currency_code }} {{ number_format($purchaseOrder->grand_total, 2) }}</span>
                    </div>
                </div>

                {{-- Phase 1 Note --}}
                <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg text-xs text-indigo-900">
                    <i class="fa-solid fa-info-circle mr-1 text-indigo-600"></i>
                    <strong>Phase 1 Direct Order:</strong> Physical delivery, offline invoicing, and settlement take place directly between buyer and supplier. Coordinate directly using the contact details provided.
                </div>
            </x-backend.form-card>

            {{-- Line Items --}}
            <x-backend.form-card title="Purchased Items">
                <div class="overflow-x-auto -mx-5 -mb-5">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-y border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Item Description</th>
                                <th class="px-3 py-3">Quantity</th>
                                <th class="px-3 py-3">Unit Price</th>
                                <th class="px-5 py-3 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-3 py-3">{{ (float)$item->quantity }}</td>
                                    <td class="px-3 py-3">{{ $purchaseOrder->currency_code }} {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 font-bold text-right text-gray-900">{{ $purchaseOrder->currency_code }} {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200 bg-gray-50 text-xs">
                            <tr>
                                <th colspan="3" class="px-5 py-3 text-right font-bold text-gray-700">Total:</th>
                                <th class="px-5 py-3 text-right font-bold text-indigo-700 text-sm">
                                    {{ $purchaseOrder->currency_code }} {{ number_format($purchaseOrder->grand_total, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-backend.form-card>

            {{-- Status History --}}
            @if($purchaseOrder->statusHistory->isNotEmpty())
                <x-backend.form-card title="Order Lifecycle History">
                    <ul class="space-y-3 text-xs">
                        @foreach($purchaseOrder->statusHistory as $history)
                            <li class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-gray-900">{{ ucfirst($history->from_status) }} &rarr; {{ ucfirst($history->to_status) }}</span>
                                    <p class="text-gray-500 mt-0.5">{{ $history->note }} &middot; by {{ $history->changedBy?->name ?? 'System' }}</p>
                                </div>
                                <span class="text-gray-400">{{ $history->created_at->format('d M Y, h:i A') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

        </div>

        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Buyer Contact Information">
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-gray-900 text-sm">{{ $purchaseOrder->buyerAccount?->buyerProfile?->organization_name ?? $purchaseOrder->buyerAccount?->display_name }}</p>
                    <p class="text-gray-600"><i class="fa-solid fa-user mr-1.5 text-gray-400"></i>{{ $purchaseOrder->buyerAccount?->buyerProfile?->contact_person ?? 'Purchasing Officer' }}</p>
                    <p class="text-gray-600"><i class="fa-solid fa-envelope mr-1.5 text-gray-400"></i>{{ $purchaseOrder->buyerAccount?->buyerProfile?->contact_email ?? $purchaseOrder->buyerAccount?->primaryOwner?->email }}</p>
                    <p class="text-gray-600"><i class="fa-solid fa-phone mr-1.5 text-gray-400"></i>{{ $purchaseOrder->buyerAccount?->buyerProfile?->contact_phone ?? 'N/A' }}</p>
                    <p class="text-gray-600"><i class="fa-solid fa-location-dot mr-1.5 text-gray-400"></i>{{ $purchaseOrder->buyerAccount?->buyerProfile?->address ?? 'N/A' }}</p>
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
