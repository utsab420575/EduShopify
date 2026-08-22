@extends('backend.layouts.admin')

@section('title', 'Conversations')
@section('breadcrumb', 'Communication / Conversations')

@section('body')

    <x-backend.page-header title="Conversations" subtitle="Buyer &ndash; supplier messaging, platform-wide." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="open" @selected($status === 'open')>Open</option>
                    <option value="closed" @selected($status === 'closed')>Closed</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($conversations->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-comments" title="No conversations found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Participants</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Context</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Message</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($conversations as $conversation)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $conversation->accounts->pluck('display_name')->implode(' & ') ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($conversation->context_type) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $conversation->last_message_at?->diffForHumans() ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$conversation->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.communication.conversations.show', $conversation) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$conversations" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
