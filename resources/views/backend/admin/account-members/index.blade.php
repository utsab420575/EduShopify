@extends('backend.layouts.admin')

@section('title', 'Account Members')
@section('breadcrumb', 'Users & Accounts / Account Members')

@section('body')

    <x-backend.page-header title="Account Members" subtitle="Platform-wide membership oversight across all organization and team accounts." />

    <x-backend.live-filter id="members" :action="route('admin.account-members.index')" :search="$search"
                           placeholder="Search member, email or account..." :filters="$filters" :counts="$counts" />

    {{-- Table + its row modals, swapped in place by the live filter above. --}}
    <div id="members-results">
        @include('backend.admin.account-members.partials._table')
    </div>

@endsection
