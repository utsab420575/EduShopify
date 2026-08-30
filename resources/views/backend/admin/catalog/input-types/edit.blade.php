@extends('backend.layouts.admin')

@section('title', 'Edit Input Type')
@section('breadcrumb', 'Catalog & Taxonomy / Input Types / Edit')

@section('body')

    <x-backend.page-header title="Edit Input Type" subtitle="Update input format properties and option rules." />

    <form method="POST" action="{{ route('admin.catalog.input-types.update', $inputType) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('backend.admin.catalog.input-types._form', ['inputType' => $inputType])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.catalog.input-types.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
