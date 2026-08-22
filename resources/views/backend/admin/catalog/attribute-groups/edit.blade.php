@extends('backend.layouts.admin')

@section('title', 'Edit ' . $group->name)
@section('breadcrumb', 'Catalog & Taxonomy / Attribute Groups / Edit')

@section('body')

    <x-backend.page-header :title="'Edit ' . $group->name" subtitle="Update specification group details." />

    <form method="POST" action="{{ route('admin.catalog.attribute-groups.update', $group) }}" class="space-y-6">
        @csrf @method('PUT')
        @include('backend.admin.catalog.attribute-groups._form', ['group' => $group])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.attribute-groups.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
