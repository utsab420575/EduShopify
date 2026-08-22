@extends('backend.layouts.admin')

@section('title', $ticket->subject)
@section('breadcrumb', 'Support / Tickets / ' . $ticket->subject)

@section('body')

    <x-backend.page-header :title="$ticket->subject" :subtitle="$ticket->ticket_number . ' — ' . $ticket->account?->display_name">
        <x-slot:actions>
            <x-backend.status-badge :status="$ticket->priority" />
            <x-backend.status-badge :status="$ticket->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if(!$ticket->assigned_admin_user_id)
            <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Assign to Me</button>
            </form>
        @endif
        @if(!in_array($ticket->status, ['resolved', 'closed']))
            <form method="POST" action="{{ route('admin.tickets.resolve', $ticket) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Resolve</button>
            </form>
            <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Close</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reopen</button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Original Request">
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $ticket->description }}</p>
            </x-backend.form-card>

            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 max-h-[28rem] overflow-y-auto">
                @forelse($ticket->messages as $message)
                    <div class="flex gap-3 {{ is_null($message->sender_account_id) ? 'justify-end' : '' }}">
                        <div class="max-w-md {{ is_null($message->sender_account_id) ? 'text-right' : '' }}">
                            <p class="text-xs text-gray-400 mb-1">
                                {{ is_null($message->sender_account_id) ? ($message->senderUser?->name.' (Admin)') : $message->senderUser?->name }}
                                @if($message->is_internal_note) <span class="text-amber-500 font-medium">&middot; Internal note</span> @endif
                                &middot; {{ $message->created_at->format('d M Y H:i') }}
                            </p>
                            <div class="inline-block rounded-lg px-3 py-2 text-sm {{ $message->is_internal_note ? 'bg-amber-50 text-amber-800 border border-amber-200' : (is_null($message->sender_account_id) ? 'text-white' : 'bg-gray-100 text-gray-800') }}"
                                 @if(!$message->is_internal_note && is_null($message->sender_account_id)) style="background:var(--theme-primary)" @endif>
                                {{ $message->message }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">No replies yet.</p>
                @endforelse
            </div>

            @if(!in_array($ticket->status, ['closed']))
                <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    @csrf
                    <x-backend.textarea name="message" :rows="3" required placeholder="Write a reply..." />
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="is_internal_note" value="1" style="accent-color:var(--theme-primary)">
                            Internal note (not visible to the account)
                        </label>
                        <button type="submit" class="btn-primary text-sm font-medium px-5 py-2.5 rounded-lg">Send</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Ticket Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $ticket->account?->display_name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Created By</dt><dd class="font-medium text-gray-900">{{ $ticket->createdBy?->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Assigned To</dt><dd class="font-medium text-gray-900">{{ $ticket->assignedAdmin?->name ?? 'Unassigned' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $ticket->created_at->format('d M Y') }}</dd></div>
                </dl>
            </x-backend.form-card>
        </div>
    </div>

@endsection
