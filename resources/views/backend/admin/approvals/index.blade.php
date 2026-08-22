@extends('backend.layouts.admin')

@section('title', 'Approval Center')
@section('breadcrumb', 'Approval Center')

@section('body')

    <x-backend.page-header title="Approval Center" subtitle="Everything awaiting your review, in one place." />

    @if(empty($queues))
        <x-backend.empty-state icon="fa-clipboard-check" title="Nothing to review" description="You don't have any pending approval queues assigned." />
    @else
        <div class="flex items-center gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach($queues as $key => $queue)
                <a href="{{ route('admin.approvals.index', ['tab' => $key]) }}"
                   class="px-4 py-2.5 text-sm whitespace-nowrap flex items-center gap-2"
                   style="{{ $tab === $key ? 'color:var(--theme-primary);border-color:var(--theme-primary)' : '' }}"
                   @class(['border-b-2 font-semibold' => $tab === $key, 'text-gray-500' => $tab !== $key])>
                    {{ $queue['label'] }}
                    @if($queue['count'] > 0)
                        <span class="text-xs font-semibold rounded-full px-1.5 py-0.5" style="background:var(--theme-primary-light,#EEF2FF);color:var(--theme-primary)">{{ $queue['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <x-backend.table>
            @if($items->isEmpty())
                <x-slot:empty><x-backend.empty-state icon="fa-circle-check" title="Queue is empty" description="Nothing pending review in this queue right now." /></x-slot:empty>
            @else
                <x-slot:head>
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted By</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </x-slot:head>
                @foreach($items as $item)
                    <tr class="hover:bg-gray-50">
                        @switch($tab)
                            @case('capabilities')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->account?->display_name }} — {{ $item->capabilityType?->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->appliedBy?->name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->applied_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.capabilities.show', $item) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break

                            @case('documents')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->supplierAccount?->supplierProfile?->display_name ?? $item->supplierAccount?->display_name }} — {{ $item->documentType?->name ?? $item->custom_name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">—</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.suppliers.show', $item->supplierAccount) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break

                            @case('listings')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->title ?? $item->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.catalog.listings.show', $item) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break

                            @case('categories')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.catalog.categories.suggestions.approve', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium" style="color:var(--theme-primary)">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.catalog.categories.suggestions.reject', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-red-600">Reject</button>
                                        </form>
                                    </div>
                                </td>
                                @break

                            @case('attributes')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.catalog.attributes.suggestions.approve', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium" style="color:var(--theme-primary)">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.catalog.attributes.suggestions.reject', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-red-600">Reject</button>
                                        </form>
                                    </div>
                                </td>
                                @break

                            @case('brands')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.catalog.brands.approve', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium" style="color:var(--theme-primary)">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.catalog.brands.reject', $item) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-red-600">Reject</button>
                                        </form>
                                    </div>
                                </td>
                                @break

                            @case('role_requests')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->account?->display_name }} — {{ $item->role_name ?? $item->requestedRole?->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->requestedBy?->name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.access-control.role-requests.show', $item) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break

                            @case('conversions')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $item->account?->display_name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->submittedBy?->name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->submitted_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.conversions.show', $item) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break

                            @case('reports')
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">Report on review #{{ $item->review_id }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->reportedByAccount?->display_name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.reviews.reports.show', $item) }}" class="text-sm font-medium" style="color:var(--theme-primary)">Review &rarr;</a>
                                </td>
                                @break
                        @endswitch
                    </tr>
                @endforeach
            @endif
        </x-backend.table>
    @endif

@endsection
