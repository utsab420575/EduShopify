@extends('backend.layouts.admin')

@section('title', $quotation->quotation_number)
@section('breadcrumb', 'Procurement Oversight / Quotations / ' . $quotation->quotation_number)

@section('body')

    <x-backend.page-header :title="$quotation->quotation_number" :subtitle="'For RFQ: ' . $quotation->rfq?->title">
        <x-slot:actions>
            <x-backend.status-badge :status="$quotation->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Quotation Summary">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($quotation->subtotal, 2) }} {{ $quotation->currency_code }}</dd></div>
                    <div><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">{{ number_format($quotation->tax_amount, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Shipping</dt><dd class="font-medium text-gray-900">{{ number_format($quotation->shipping_charge, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Grand Total</dt><dd class="font-semibold text-gray-900">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</dd></div>
                    <div><dt class="text-gray-500">Lead Time</dt><dd class="font-medium text-gray-900">{{ $quotation->lead_time_days }} days</dd></div>
                    <div><dt class="text-gray-500">Valid Until</dt><dd class="font-medium text-gray-900">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
                @if($quotation->proposal)
                    <p class="text-sm text-gray-600 mt-4 border-t border-gray-100 pt-4">{{ $quotation->proposal }}</p>
                @endif
            </x-backend.form-card>

            @if($quotation->items->isNotEmpty())
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
                                @foreach($quotation->items as $item)
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

            @if($quotation->rejection_comment)
                <x-backend.form-card title="Rejection Comment"><p class="text-sm text-gray-600">{{ $quotation->rejection_comment }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Supplier">
                <p class="text-sm font-medium text-gray-900">{{ $quotation->supplierAccount?->supplierProfile?->display_name }}</p>
                <a href="{{ route('admin.suppliers.show', $quotation->supplierAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
            </x-backend.form-card>

            <x-backend.form-card title="RFQ">
                <p class="text-sm font-medium text-gray-900">{{ $quotation->rfq?->title }}</p>
                <a href="{{ route('admin.procurement.rfqs.show', $quotation->rfq) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View RFQ &rarr;</a>
            </x-backend.form-card>

            @if($quotation->award)
                <x-backend.form-card title="Award">
                    <x-backend.status-badge :status="$quotation->award->status" />
                    <a href="{{ route('admin.procurement.awards.show', $quotation->award) }}" class="text-sm font-medium mt-2 block" style="color:var(--theme-primary)">View Award &rarr;</a>
                </x-backend.form-card>
            @endif
        </div>
    </div>

@endsection
