@extends('backend.layouts.supplier')

@section('title', 'Direct Messages')
@section('breadcrumb', 'Communication / Messages')

@section('body')

    <x-backend.page-header title="Messages" subtitle="Communicate directly with buyers regarding RFQs, product inquiries, and orders." />

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($conversations->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-comments" title="No active conversations" description="Messages between you and buyers regarding inquiries and RFQs will appear here." />
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($conversations as $conv)
                    @php
                        $other = $conv->accounts->first();
                        $lastMessage = $conv->messages->first();
                    @endphp
                    <a href="{{ route('supplier.messages.show', $conv) }}" class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors block">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($other?->display_name ?? 'Buyer') }}&background=0D9488&color=fff" class="w-10 h-10 rounded-full shrink-0" alt="">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $other?->buyerProfile?->organization_name ?? $other?->display_name ?? 'Institutional Buyer' }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $lastMessage?->body ?? 'No messages yet' }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[11px] text-gray-400 block">{{ $conv->last_message_at?->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            @if($conversations->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $conversations->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
