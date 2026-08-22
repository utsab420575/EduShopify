@extends('backend.layouts.buyer')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Procurement / Purchase Orders')

@section('body')

    <x-backend.page-header title="Purchase Orders" subtitle="Orders issued after an award is accepted by the supplier." />

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('buyer.purchase-orders.index') }}" class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $status === '' ? 'text-white' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}" @if($status === '') style="background:var(--theme-primary);border-color:var(--theme-primary)" @endif>All</a>
        @foreach($statusOptions as $value => $label)
            <a href="{{ route('buyer.purchase-orders.index', ['status' => $value]) }}" class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $status === $value ? 'text-white' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}" @if($status === $value) style="background:var(--theme-primary);border-color:var(--theme-primary)" @endif>{{ $label }}</a>
        @endforeach
    </div>

    <x-backend.table>
        @if($orders->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-clipboard-list" title="No purchase orders yet" description="Purchase orders are created automatically once a supplier accepts an award." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">PO #</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($orders as $po)
                <tr class="hover:bg-gray-50">
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
