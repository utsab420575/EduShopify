@extends('backend.layouts.admin')

@section('title', 'Awards')
@section('breadcrumb', 'Procurement Oversight / Awards')

@section('body')

    <x-backend.page-header title="Awards" subtitle="Platform-wide award history." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending_supplier_response' => 'Pending Response', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'expired' => 'Expired'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($awards->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-trophy" title="No awards found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Award</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Buyer</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Awarded</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($awards as $award)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $award->award_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->buyerAccount?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->awarded_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$award->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.procurement.awards.show', $award) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$awards" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
