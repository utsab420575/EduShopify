@extends('backend.layouts.admin')

@section('title', 'Notifications')
@section('breadcrumb', 'Communication / Notifications')

@section('body')

    <x-backend.page-header title="Notifications" subtitle="Platform activity relevant to you.">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Mark all read</button>
            </form>
        </x-slot:actions>
    </x-backend.page-header>

    @if($notifications->isEmpty())
        <x-backend.empty-state icon="fa-bell" title="No notifications yet" description="You're all caught up." />
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden divide-y divide-gray-100">
            @foreach($notifications as $notification)
                <div class="flex items-start justify-between gap-3 px-5 py-4 {{ $notification->read_at ? '' : 'bg-gray-50' }}">
                    <div class="flex gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:var(--theme-primary-soft)">
                            <i class="fa-solid fa-bell text-xs" style="color:var(--theme-primary)"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">{{ $notification->data['message'] ?? ($notification->type) }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium whitespace-nowrap" style="color:var(--theme-primary)">Mark read</button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$notifications" />
        </div>
    @endif

@endsection
