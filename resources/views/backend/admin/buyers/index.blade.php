@extends('backend.layouts.admin')

@section('title', 'Buyers')
@section('breadcrumb', 'Users & Accounts / Buyers')

@section('body')

    <x-backend.page-header title="Buyers" subtitle="Accounts with a Buyer capability." />

    <x-backend.live-filter id="buyers" :action="route('admin.buyers.index')" :search="$search"
                           placeholder="Search name, account number, contact or email..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="buyers-results">
        @include('backend.admin.buyers.partials._table')
    </div>

@endsection
