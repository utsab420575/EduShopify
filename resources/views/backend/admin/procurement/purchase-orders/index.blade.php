@extends('backend.layouts.admin')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Procurement Oversight / Purchase Orders')

@section('body')

    <x-backend.page-header title="Purchase Orders" subtitle="Platform-wide purchase order fulfillment." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['issued' => 'Issued', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'delivered' => 'Delivered', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($purchaseOrders->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-truck" title="No purchase orders found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">PO</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Buyer</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($purchaseOrders as $po)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $po->po_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $po->buyerAccount?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $po->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ number_format($po->grand_total, 2) }} {{ $po->currency_code }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$po->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.procurement.purchase-orders.show', $po) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$purchaseOrders" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
