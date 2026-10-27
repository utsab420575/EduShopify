@extends('backend.layouts.admin')

@section('title', 'Account Conversions')
@section('breadcrumb', 'Users & Accounts / Conversions')

@section('body')

    <x-backend.page-header title="Account Conversions" subtitle="Individual buyer accounts requesting to convert to an organization." />

    <x-backend.live-filter id="conversions" :action="route('admin.conversions.index')" :search="$search"
                           placeholder="Search account or proposed name..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="conversions-results">
        @include('backend.admin.conversions.partials._table')
    </div>

@endsection
