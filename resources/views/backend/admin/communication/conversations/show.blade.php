@extends('backend.layouts.admin')

@section('title', 'Conversation')
@section('breadcrumb', 'Communication / Conversations / Detail')

@section('body')

    <x-backend.page-header :title="$conversation->accounts->pluck('display_name')->implode(' & ') ?: 'Conversation'" :subtitle="ucfirst($conversation->context_type)">
        <x-slot:actions>
            <x-backend.status-badge :status="$conversation->status" />
            @unless($isJoined)
                <form method="POST" action="{{ route('admin.communication.conversations.join', $conversation) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Join Conversation</button>
                </form>
            @endunless
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 mb-6 max-h-[32rem] overflow-y-auto">
        @forelse($conversation->messages as $message)
            <div class="flex gap-3 {{ $message->isFromPlatformStaff() ? 'justify-end' : '' }}">
                <div class="max-w-md {{ $message->isFromPlatformStaff() ? 'text-right' : '' }}">
                    <p class="text-xs text-gray-400 mb-1">{{ $message->isFromPlatformStaff() ? ($message->senderUser?->name.' (Admin)') : $message->senderAccount?->display_name }} &middot; {{ $message->created_at->format('d M Y H:i') }}</p>
                    <div class="inline-block rounded-lg px-3 py-2 text-sm {{ $message->isFromPlatformStaff() ? 'text-white' : 'bg-gray-100 text-gray-800' }}" @if($message->isFromPlatformStaff()) style="background:var(--theme-primary)" @endif>
                        {{ $message->body }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-8">No messages yet.</p>
        @endforelse
    </div>

    @if($isJoined)
        <form method="POST" action="{{ route('admin.communication.conversations.store', $conversation) }}" class="bg-white rounded-xl border border-gray-200 p-4 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <x-backend.textarea name="body" :rows="2" required placeholder="Type a message as platform support..." />
            </div>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2.5 rounded-lg">Send</button>
        </form>
    @endif

@endsection
