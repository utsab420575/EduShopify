@extends('backend.layouts.buyer')

@section('title', 'Received Quotations')
@section('breadcrumb', 'Procurement / Quotations')

@section('body')

    <x-backend.page-header title="Received Quotations" subtitle="Quotations submitted by suppliers against your RFQs." />

    <x-backend.form-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-backend.select name="rfq" label="RFQ" placeholder="All RFQs">
                @foreach($rfqOptions as $option)
                    <option value="{{ $option->id }}" @selected($rfq === $option->id)>{{ $option->title }} ({{ $option->rfq_number }})</option>
                @endforeach
            </x-backend.select>
            <x-backend.select name="status" label="Status" :options="$statusOptions" placeholder="All Statuses" :selected="$status" />
            <div class="flex items-end">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg w-full">Filter</button>
            </div>
        </form>
    </x-backend.form-card>

    <x-backend.table>
        @if($quotations->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-inbox" title="No quotations found" description="There are no quotations matching the selected filters." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quote #</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($quotations as $quotation)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $quotation->supplierAccount?->supplierProfile?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $quotation->submitted_at?->format('d M Y') }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->rfq->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->quotation_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$quotation->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.quotations.show', $quotation) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$quotations" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
