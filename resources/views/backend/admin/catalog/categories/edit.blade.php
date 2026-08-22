@extends('backend.layouts.admin')

@section('title', 'Edit ' . $category->name)
@section('breadcrumb', 'Catalog & Taxonomy / Categories / Edit')

@section('body')

    <x-backend.page-header :title="'Edit ' . $category->name" />

    <form method="POST" action="{{ route('admin.catalog.categories.update', $category) }}" class="space-y-6">
        @csrf @method('PUT')
        @include('backend.admin.catalog.categories._form', ['category' => $category, 'parents' => $parents])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.categories.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
