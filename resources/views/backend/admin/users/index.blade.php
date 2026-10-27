@extends('backend.layouts.admin')

@section('title', 'Users')
@section('breadcrumb', 'Users & Accounts / Users')

@section('body')

    <x-backend.page-header title="Users" subtitle="Every login on the platform." />

    <x-backend.live-filter id="users" :action="route('admin.users.index')" :search="$search"
                           placeholder="Search name, email or phone..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="users-results">
        @include('backend.admin.users.partials._table')
    </div>

@endsection
