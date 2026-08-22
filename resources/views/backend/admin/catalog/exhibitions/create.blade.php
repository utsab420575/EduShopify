@extends('backend.layouts.admin')

@section('title', 'New Exhibition')
@section('breadcrumb', 'Catalog & Taxonomy / Exhibitions / New')

@section('body')

    <x-backend.page-header title="New Exhibition" />

    <form method="POST" action="{{ route('admin.catalog.exhibitions.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.catalog.exhibitions._form', ['exhibition' => $exhibition])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.exhibitions.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Exhibition</button>
        </div>
    </form>

@endsection
