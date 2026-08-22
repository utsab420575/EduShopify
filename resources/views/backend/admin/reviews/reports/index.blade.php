@extends('backend.layouts.admin')

@section('title', 'Review Reports')
@section('breadcrumb', 'Reviews & Moderation / Reports')

@section('body')

    <x-backend.page-header title="Review Reports" subtitle="Reviews flagged by buyers or suppliers." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search reporter..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'dismissed' => 'Dismissed', 'actioned' => 'Actioned'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($reports->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-flag" title="No reports found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reported By</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($reports as $report)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $report->reportedByAccount?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $report->reason }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $report->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$report->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.reviews.reports.dismiss', $report) }}" onsubmit="return confirm('Dismiss this report without action?');">
                                    @csrf
                                    <button type="submit" title="Dismiss" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-ban"></i></button>
                                </form>
                                <button type="button" title="Take Action" @click="$dispatch('open-modal-action-report-{{ $report->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-gavel"></i></button>
                            @endif
                            <a href="{{ route('admin.reviews.reports.show', $report) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$reports" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($reports as $report)
        @if($report->status === 'pending')
            <x-backend.modal :id="'action-report-'.$report->id" title="Take Action on Report">
                <form method="POST" action="{{ route('admin.reviews.reports.action-taken', $report) }}">
                    @csrf
                    <x-backend.select name="review_action" label="Action" required :options="['hidden' => 'Hide the review', 'rejected' => 'Reject the review', 'warned' => 'Warn the reviewer']" />
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Take Action</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
