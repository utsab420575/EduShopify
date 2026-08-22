@extends('backend.layouts.admin')

@section('title', 'Edit ' . $unit->name)
@section('breadcrumb', 'Catalog & Taxonomy / Units / Edit')

@section('body')

    <x-backend.page-header :title="'Edit ' . $unit->name">
        <x-slot:actions>
            <x-backend.status-badge :status="$unit->approval_status" />
        </x-slot:actions>
    </x-backend.page-header>

    <form method="POST" action="{{ route('admin.catalog.units.update', $unit) }}" class="space-y-6">
        @csrf @method('PUT')
        @include('backend.admin.catalog.units._form', ['unit' => $unit])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.units.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
