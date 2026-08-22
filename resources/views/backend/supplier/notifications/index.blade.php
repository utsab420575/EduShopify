@extends('backend.layouts.supplier')

@section('title', 'Notifications')
@section('breadcrumb', 'Communication / Notifications')

@section('body')

    <x-backend.page-header title="Notifications" subtitle="Stay updated with RFQ invitations, quotations, awards, and account alerts.">
        <x-slot:actions>
            <form method="POST" action="{{ route('supplier.notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Mark All as Read
                </button>
            </form>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-bell" title="You're all caught up" description="No new system notifications at this time." />
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($notifications as $notif)
                    <div class="p-4 flex items-center justify-between gap-4 {{ $notif->read_at ? 'bg-white' : 'bg-indigo-50/20' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $notif->read_at ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700' }}">
                                <i class="fa-solid fa-bell text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-900">{{ $notif->data['message'] ?? 'Notification alert' }}</p>
                                <span class="text-[10px] text-gray-400 mt-0.5 block">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if(!$notif->read_at)
                            <form method="POST" action="{{ route('supplier.notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="text-[11px] text-indigo-600 font-semibold hover:underline">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
            @if($notifications->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
