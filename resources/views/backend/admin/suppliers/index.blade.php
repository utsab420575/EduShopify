@extends('backend.layouts.admin')

@section('title', 'Suppliers')
@section('breadcrumb', 'Users & Accounts / Suppliers')

@section('body')

    <x-backend.page-header title="Suppliers" subtitle="Accounts with a Supplier capability." />

    <x-backend.live-filter id="suppliers" :action="route('admin.suppliers.index')" :search="$search"
                           placeholder="Search name, account number, legal name or email..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="suppliers-results">
        @include('backend.admin.suppliers.partials._table')
    </div>

@endsection
