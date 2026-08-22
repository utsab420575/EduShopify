@extends('backend.layouts.master')

@php
    $user = $user ?? auth()->user();
    $account = $account ?? $user?->activateTeamContext() ?? $user?->currentAccount;
@endphp

@section('sidebar')
    @include('backend.layouts.partials.buyer._sidebar', ['account' => $account, 'user' => $user])
@endsection

@section('topbar')
    @include('backend.layouts.partials.buyer._topbar', [
        'account' => $account,
        'user' => $user,
        'unreadNotifications' => $unreadNotifications ?? 0,
        'unreadMessages' => $unreadMessages ?? 0,
        'topbarNotifications' => $topbarNotifications ?? [],
    ])
@endsection

@section('content')
    @include('backend.layouts.partials.shared._flash')
    @yield('body')
@endsection
