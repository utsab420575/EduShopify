@extends('backend.layouts.supplier')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Procurement / Purchase Orders')

@section('body')

    <x-backend.page-header title="Purchase Orders" subtitle="Manage purchase orders generated from accepted awards and coordinate fulfilment." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('supplier.purchase-orders.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="issued" @selected($status === 'issued')>Issued (Open)</option>
                <option value="completed" @selected($status === 'completed')>Completed</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2.5 rounded-lg">
                Filter
            </button>
            @if($status)
                <a href="{{ route('supplier.purchase-orders.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2">Reset</a>
            @endif
        </form>
    </div>

    {{-- PO Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($purchaseOrders->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-clipboard-list" title="No purchase orders yet" description="When you accept an award, a purchase order will be generated here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">PO Number</th>
                            <th class="px-3 py-3.5 font-semibold">Buyer</th>
                            <th class="px-3 py-3.5 font-semibold">RFQ</th>
                            <th class="px-3 py-3.5 font-semibold">Total Amount</th>
                            <th class="px-3 py-3.5 font-semibold">Issued Date</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="font-bold text-gray-900 hover:text-indigo-600">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    {{ $po->buyerAccount?->buyerProfile?->organization_name ?? $po->buyerAccount?->display_name }}
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-700 font-medium">
                                    {{ $po->rfq?->title ?? 'RFQ #' . $po->rfq_id }}
                                </td>
                                <td class="px-3 py-3.5 font-bold text-indigo-700 text-xs">
                                    {{ $po->currency_code }} {{ number_format($po->grand_total, 2) }}
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-500">
                                    {{ $po->issued_at?->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$po->status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg inline-block">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($purchaseOrders->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
