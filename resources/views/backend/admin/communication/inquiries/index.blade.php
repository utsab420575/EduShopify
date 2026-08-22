@extends('backend.layouts.admin')

@section('title', 'Contact Inquiries')
@section('breadcrumb', 'Communication / Inquiries')

@section('body')

    <x-backend.page-header title="Contact Inquiries" subtitle="Guest enquiries submitted through the platform or supplier storefronts." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, subject..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($inquiries->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-envelope" title="No inquiries found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">From</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Target</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Received</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($inquiries as $inquiry)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $inquiry->name }}</p>
                        <p class="text-xs text-gray-400">{{ $inquiry->email }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $inquiry->subject ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $inquiry->isPlatformInquiry() ? 'Platform' : ($inquiry->supplierAccount?->supplierProfile?->display_name ?? '—') }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $inquiry->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$inquiry->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.communication.inquiries.show', $inquiry) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$inquiries" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
