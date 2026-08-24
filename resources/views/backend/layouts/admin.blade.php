@extends('backend.layouts.master')

@section('sidebar')
    @include('backend.layouts.partials.admin._sidebar', ['user' => $user ?? auth()->user(), 'approvalQueueTotal' => $approvalQueueTotal ?? 0, 'approvalQueues' => $approvalQueues ?? []])
@endsection

@section('topbar')
    @include('backend.layouts.partials.admin._topbar', [
        'user' => $user ?? auth()->user(),
        'unreadNotifications' => $unreadNotifications ?? 0,
        'topbarNotifications' => $topbarNotifications ?? [],
    ])
@endsection

@section('content')
    @include('backend.layouts.partials.shared._flash')
    @yield('body')
@endsection
