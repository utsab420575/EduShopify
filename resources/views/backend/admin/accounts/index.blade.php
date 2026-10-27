@extends('backend.layouts.admin')

@section('title', 'Accounts')
@section('breadcrumb', 'Users & Accounts / Accounts')

@section('body')

    <x-backend.page-header title="Accounts" subtitle="Every marketplace account on the platform." />

    <x-backend.live-filter id="accounts" :action="route('admin.accounts.index')" :search="$search"
                           placeholder="Search name, account number or owner..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="accounts-results">
        @include('backend.admin.accounts.partials._table')
    </div>

@endsection
