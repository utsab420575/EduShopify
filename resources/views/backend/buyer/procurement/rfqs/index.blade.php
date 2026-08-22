@extends('backend.layouts.buyer')

@section('title', 'My RFQs')
@section('breadcrumb', 'Procurement / RFQs')

@section('body')

    <x-backend.page-header title="My RFQs" subtitle="Manage your requests for quotation.">
        <x-slot:actions>
            @can('create', \App\Models\Rfq::class)
                <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Create RFQ
                </a>
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('buyer.rfqs.index', array_filter(['search' => $search])) }}"
           class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $status === '' ? 'text-white' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}"
           @if($status === '') style="background:var(--theme-primary);border-color:var(--theme-primary)" @endif>All</a>
        @foreach($statusOptions as $value => $label)
            <a href="{{ route('buyer.rfqs.index', array_filter(['status' => $value, 'search' => $search])) }}"
               class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $status === $value ? 'text-white' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}"
               @if($status === $value) style="background:var(--theme-primary);border-color:var(--theme-primary)" @endif>{{ $label }}</a>
        @endforeach
    </div>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="relative w-full sm:w-64">
                <input type="hidden" name="status" value="{{ $status }}">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search RFQ number or title..."
                       class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
            </form>
        </x-slot:toolbar>

        @if($rfqs->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-file-signature" title="No RFQs found" description="There are no RFQs matching the selected filters.">
                    <x-slot:actions>
                        @can('create', \App\Models\Rfq::class)
                            <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Create RFQ</a>
                        @endcan
                    </x-slot:actions>
                </x-backend.empty-state>
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quotes</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($rfqs as $rfq)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $rfq->title }}</p>
                        <p class="text-xs text-gray-400">{{ $rfq->rfq_number }} &middot; {{ $rfq->created_at->format('d M Y') }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $rfq->items_count }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $rfq->quotations_count }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $rfq->quotation_deadline?->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$rfq->status" /></td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($rfq->status === 'draft')
                                <a href="{{ route('buyer.rfqs.edit', $rfq) }}" title="Continue Editing" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-pen"></i></a>
                            @endif
                            <a href="{{ route('buyer.rfqs.show', $rfq) }}" title="View" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$rfqs" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
