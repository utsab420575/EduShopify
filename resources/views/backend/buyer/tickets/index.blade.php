@extends('backend.layouts.buyer')

@section('title', 'Support Tickets')
@section('breadcrumb', 'Communication / Support Tickets')

@section('body')

    <div x-data="{ showCreate: false }">
        <x-backend.page-header title="Support Tickets" subtitle="Get help from the EduShopify support team.">
            <x-slot:actions>
                <button @click="showCreate = !showCreate" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> New Ticket
                </button>
            </x-slot:actions>
        </x-backend.page-header>

        <div x-show="showCreate" x-transition x-cloak class="mb-6">
            <x-backend.form-card title="New Support Ticket">
                <form method="POST" action="{{ route('buyer.tickets.store') }}" class="space-y-4">
                    @csrf
                    <x-backend.input name="subject" label="Subject" required />
                    <x-backend.select name="priority" label="Priority" required :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']" selected="normal" />
                    <x-backend.textarea name="description" label="Description" required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCreate = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Submit Ticket</button>
                    </div>
                </form>
            </x-backend.form-card>
        </div>
    </div>

    <x-backend.table>
        @if($tickets->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-life-ring" title="No support tickets" description="Need help? Open a new ticket above." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket #</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($tickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $ticket->subject }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $ticket->ticket_number }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$ticket->priority" /></td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$ticket->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.tickets.show', $ticket) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$tickets" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
