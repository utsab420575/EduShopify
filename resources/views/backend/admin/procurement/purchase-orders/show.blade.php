@extends('backend.layouts.admin')

@section('title', $purchaseOrder->po_number)
@section('breadcrumb', 'Procurement Oversight / Purchase Orders / ' . $purchaseOrder->po_number)

@section('body')

    <x-backend.page-header :title="$purchaseOrder->po_number" subtitle="Purchase order details">
        <x-slot:actions>
            <x-backend.status-badge :status="$purchaseOrder->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Order Summary">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->subtotal, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->tax_amount, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Shipping</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->shipping_charge, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Grand Total</dt><dd class="font-semibold text-gray-900">{{ number_format($purchaseOrder->grand_total, 2) }} {{ $purchaseOrder->currency_code }}</dd></div>
                    <div><dt class="text-gray-500">Payment Status</dt><dd class="font-medium text-gray-900">{{ ucfirst($purchaseOrder->payment_status ?? '—') }}</dd></div>
                    <div><dt class="text-gray-500">Issued</dt><dd class="font-medium text-gray-900">{{ $purchaseOrder->issued_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if($purchaseOrder->items->isNotEmpty())
                <x-backend.form-card title="Line Items">
                    <div class="overflow-x-auto -mx-5">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                                    <th class="px-5 py-2 text-left">Item</th>
                                    <th class="px-5 py-2 text-left">Qty</th>
                                    <th class="px-5 py-2 text-left">Unit Price</th>
                                    <th class="px-5 py-2 text-left">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    <tr class="border-b border-gray-50">
                                        <td class="px-5 py-2.5 text-gray-900">{{ $item->item_name ?? '—' }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ $item->quantity }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ number_format($item->line_total ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-backend.form-card>
            @endif

            @if($purchaseOrder->statusHistory->isNotEmpty())
                <x-backend.form-card title="Status History">
                    <ul class="space-y-3">
                        @foreach($purchaseOrder->statusHistory as $history)
                            <li class="text-sm flex justify-between">
                                <span class="text-gray-700">{{ ucfirst($history->status ?? $history->to_status ?? '') }}</span>
                                <span class="text-xs text-gray-400">{{ $history->created_at->format('d M Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Parties">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-500">Buyer</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.buyers.show', $purchaseOrder->buyerAccount) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $purchaseOrder->buyerAccount?->display_name }}</a></dd></div>
                    <div><dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.suppliers.show', $purchaseOrder->supplierAccount) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $purchaseOrder->supplierAccount?->supplierProfile?->display_name ?? $purchaseOrder->supplierAccount?->display_name }}</a></dd></div>
                </dl>
            </x-backend.form-card>

            <x-backend.form-card title="Related Records">
                <div class="space-y-2 text-sm">
                    <a href="{{ route('admin.procurement.rfqs.show', $purchaseOrder->rfq) }}" class="block hover:underline" style="color:var(--theme-primary)">View RFQ &rarr;</a>
                    <a href="{{ route('admin.procurement.awards.show', $purchaseOrder->award) }}" class="block hover:underline" style="color:var(--theme-primary)">View Award &rarr;</a>
                </div>
            </x-backend.form-card>
        </div>
    </div>

@endsection
