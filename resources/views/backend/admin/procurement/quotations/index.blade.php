@extends('backend.layouts.admin')

@section('title', 'Quotations')
@section('breadcrumb', 'Procurement Oversight / Quotations')

@section('body')

    <x-backend.page-header title="Quotations" subtitle="Platform-wide supplier quotations." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'submitted' => 'Submitted', 'under_review' => 'Under Review', 'revised' => 'Revised', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'awarded' => 'Awarded', 'withdrawn' => 'Withdrawn'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($quotations->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-file-invoice-dollar" title="No quotations found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quotation</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($quotations as $quotation)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $quotation->quotation_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->rfq?->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $quotation->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$quotation->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.procurement.quotations.show', $quotation) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$quotations" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
