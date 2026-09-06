@extends('backend.layouts.buyer')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Procurement / Purchase Orders')

@section('body')

    <x-backend.page-header title="Purchase Orders" subtitle="Orders issued after an award is accepted by the supplier." />

    <x-backend.tabs>
        <x-backend.tab :href="route('buyer.purchase-orders.index')" :active="$status === ''">All</x-backend.tab>
        @foreach($statusOptions as $value => $label)
            <x-backend.tab :href="route('buyer.purchase-orders.index', ['status' => $value])" :active="$status === $value">{{ $label }}</x-backend.tab>
        @endforeach
    </x-backend.tabs>

    <x-backend.table>
        @if($orders->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-clipboard-list" title="No purchase orders yet" description="Purchase orders are created automatically once a supplier accepts an award." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <x-backend.sortable-th column="po_number" label="PO #" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <x-backend.sortable-th column="grand_total" label="Total" align="right" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($orders as $po)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $orders->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $po->po_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $po->rfq->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $po->supplierAccount?->supplierProfile?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($po->grand_total, 2) }} {{ $po->currency_code }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$po->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.purchase-orders.show', $po) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$orders" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
