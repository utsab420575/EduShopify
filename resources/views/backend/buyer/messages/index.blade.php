@extends('backend.layouts.buyer')

@section('title', 'Messages')
@section('breadcrumb', 'Communication / Messages')

@section('body')

    <x-backend.page-header title="Messages" subtitle="Conversations with suppliers." />

    @if($conversations->isEmpty())
        <x-backend.empty-state icon="fa-comments" title="No conversations yet" description="Start a conversation from a supplier's profile.">
            <x-slot:actions>
                <a href="{{ route('buyer.suppliers.index') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Browse Suppliers</a>
            </x-slot:actions>
        </x-backend.empty-state>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden divide-y divide-gray-100">
            @foreach($conversations as $conversation)
                @php
                    $other = $conversation->accounts->first();
                    $lastMessage = $conversation->messages->first();
                    $state = $conversation->userStates->first();
                    $unread = $lastMessage && (! $state?->last_read_at || $state->last_read_at->lt($conversation->last_message_at ?? $conversation->created_at));
                @endphp
                <a href="{{ route('buyer.messages.show', $conversation) }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($other?->supplierProfile?->display_name ?? $other?->display_name ?? '?') }}&background=eef2ff&color=4f46e5" class="w-10 h-10 rounded-full shrink-0" alt="">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm {{ $unread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' }} truncate">{{ $other?->supplierProfile?->display_name ?? $other?->display_name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $lastMessage ? Str::limit($lastMessage->body, 80) : 'No messages yet' }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($unread)<span class="w-2 h-2 rounded-full" style="background:var(--theme-primary)"></span>@endif
                        <span class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$conversations" />
        </div>
    @endif

@endsection
