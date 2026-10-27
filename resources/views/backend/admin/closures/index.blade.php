@extends('backend.layouts.admin')

@section('title', 'Account Closures')
@section('breadcrumb', 'Users & Accounts / Closures')

@section('body')

    <x-backend.page-header title="Account Closures" subtitle="Accounts awaiting deletion — review dependencies before finalizing." />

    <x-backend.live-filter id="closures" :action="route('admin.closures.index')" :search="$search"
                           placeholder="Search account, number or owner..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="closures-results">
        @include('backend.admin.closures.partials._table')
    </div>

@endsection
