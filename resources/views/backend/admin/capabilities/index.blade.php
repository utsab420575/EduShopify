@extends('backend.layouts.admin')

@section('title', 'Capability Applications')
@section('breadcrumb', 'Users & Accounts / Capabilities')

@section('body')

    <x-backend.page-header title="Capability Applications" subtitle="Buyer and Supplier capability applications platform-wide." />

    <x-backend.live-filter id="capabilities" :action="route('admin.capabilities.index')" :search="$search"
                           placeholder="Search account name or number..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="capabilities-results">
        @include('backend.admin.capabilities.partials._table')
    </div>

@endsection
