@extends('backend.layouts.admin')

@section('title', 'Support Tickets')
@section('breadcrumb', 'Support / Tickets')

@section('body')

    <x-backend.page-header title="Support Tickets" subtitle="Buyer and supplier support requests." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search subject..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['open' => 'Open', 'answered' => 'Answered', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="priority" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Priorities</option>
                    @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($tickets->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-life-ring" title="No tickets found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($tickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->ticket_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $ticket->account?->display_name }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$ticket->priority" /></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $ticket->assignedAdmin?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$ticket->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$tickets" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
