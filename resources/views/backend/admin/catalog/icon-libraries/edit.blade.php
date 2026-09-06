@extends('backend.layouts.admin')

@section('title', 'Edit ' . $library->name)
@section('breadcrumb', 'Catalog & Taxonomy / Icon Libraries / Edit')

@section('body')

    <x-backend.page-header :title="'Edit ' . $library->name" subtitle="Update icon library configuration and status." />

    <form method="POST" action="{{ route('admin.catalog.icon-libraries.update', $library) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('backend.admin.catalog.icon-libraries._form', ['library' => $library])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.icon-libraries.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
