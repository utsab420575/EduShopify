@extends('backend.layouts.admin')

@section('title', 'New Input Type')
@section('breadcrumb', 'Catalog & Taxonomy / Input Types / New')

@section('body')

    <x-backend.page-header title="New Input Type" subtitle="Create a new catalog attribute input format." />

    <form method="POST" action="{{ route('admin.catalog.input-types.store') }}" class="space-y-6">
        @csrf

        @include('backend.admin.catalog.input-types._form', ['inputType' => $inputType])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.catalog.input-types.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Create Input Type</button>
        </div>
    </form>

@endsection
