@extends('backend.layouts.supplier')

@section('title', 'Ticket #' . $ticket->id . ' — ' . $ticket->subject)
@section('breadcrumb', 'Support Tickets / #' . $ticket->id)

@section('body')

    <x-backend.page-header title="{{ $ticket->subject }}" subtitle="Ticket #{{ $ticket->id }} &middot; Category: {{ ucfirst($ticket->category) }} &middot; Status: {{ ucfirst($ticket->status) }}" />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            {{-- Messages Thread --}}
            <div class="space-y-4">
                @foreach($ticket->messages as $msg)
                    <div class="p-4 rounded-xl border {{ $msg->sender_user_id === $user->id ? 'bg-indigo-50/30 border-indigo-200' : 'bg-white border-gray-200 shadow-sm' }}">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100 text-xs">
                            <span class="font-bold text-gray-900">{{ $msg->senderUser?->name ?? 'EduShopify Support' }}</span>
                            <span class="text-gray-400">{{ $msg->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <p class="text-xs text-gray-800 whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Reply Box --}}
            @if($ticket->status !== 'closed')
                <x-backend.form-card title="Reply to Ticket">
                    <form method="POST" action="{{ route('supplier.tickets.reply', $ticket) }}" class="space-y-4">
                        @csrf
                        <x-backend.textarea name="message" required placeholder="Type your reply to the support team..." />
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-reply"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </x-backend.form-card>
            @endif

        </div>

        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Ticket Details">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Status</span>
                        <x-backend.status-badge :status="$ticket->status" />
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Priority</span>
                        <span class="font-bold text-gray-900 uppercase">{{ $ticket->priority }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-400">Assigned Agent</span>
                        <span class="font-medium text-gray-900">{{ $ticket->assignedAdmin?->name ?? 'Helpdesk Queue' }}</span>
                    </div>
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
