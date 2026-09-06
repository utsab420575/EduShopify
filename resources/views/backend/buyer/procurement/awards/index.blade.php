@extends('backend.layouts.buyer')

@section('title', 'Awards')
@section('breadcrumb', 'Procurement / Awards')

@section('body')

    <x-backend.page-header title="Awards" subtitle="Track awards you've issued to suppliers." />

    <x-backend.tabs>
        <x-backend.tab :href="route('buyer.awards.index')" :active="$status === ''">All</x-backend.tab>
        @foreach($statusOptions as $value => $label)
            <x-backend.tab :href="route('buyer.awards.index', ['status' => $value])" :active="$status === $value">{{ $label }}</x-backend.tab>
        @endforeach
    </x-backend.tabs>

    <x-backend.table>
        @if($awards->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-trophy" title="No awards yet" description="Awards you issue to suppliers will appear here." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <x-backend.sortable-th column="award_number" label="Award #" />
                    <x-backend.sortable-th column="awarded_at" label="Awarded" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($awards as $award)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $awards->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 font-medium">{{ $award->rfq->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->supplierAccount?->supplierProfile?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->award_number }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $award->awarded_at?->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$award->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.awards.show', $award) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$awards" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
