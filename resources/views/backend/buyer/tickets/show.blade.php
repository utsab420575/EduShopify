@extends('backend.layouts.buyer')

@section('title', $ticket->subject)
@section('breadcrumb', 'Communication / Support Tickets / ' . $ticket->ticket_number)

@section('body')

    <x-backend.page-header :title="$ticket->subject" :subtitle="$ticket->ticket_number . ' — opened ' . $ticket->created_at->format('d M Y')">
        <x-slot:actions>
            <x-backend.status-badge :status="$ticket->priority" />
            <x-backend.status-badge :status="$ticket->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 mb-6">
        @foreach($ticket->messages as $message)
            @php($isSupport = $message->sender_account_id === null)
            <div class="flex gap-3 {{ $isSupport ? '' : 'flex-row-reverse' }}">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $isSupport ? 'bg-gray-100 text-gray-600' : 'text-white' }}" @if(!$isSupport) style="background:var(--theme-primary)" @endif>
                    {{ $isSupport ? 'S' : substr($message->senderUser?->name ?? '?', 0, 1) }}
                </div>
                <div class="max-w-[75%] {{ $isSupport ? 'text-left' : 'text-right' }}">
                    <div class="inline-block rounded-xl px-3.5 py-2 text-sm {{ $isSupport ? 'bg-gray-100 text-gray-800' : 'text-white' }}" @if(!$isSupport) style="background:var(--theme-primary)" @endif>
                        {{ $message->message }}
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">{{ $isSupport ? 'Support' : $message->senderUser?->name }} &middot; {{ $message->created_at->format('d M, h:i A') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if(! $ticket->isClosed())
        <form method="POST" action="{{ route('buyer.tickets.reply', $ticket) }}" class="bg-white rounded-xl border border-gray-200 p-5">
            @csrf
            <x-backend.textarea name="message" label="Reply" required />
            <div class="flex justify-end mt-3">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send Reply</button>
            </div>
        </form>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center text-sm text-gray-500">This ticket is closed.</div>
    @endif

@endsection
