@extends('backend.layouts.admin')

@section('title', 'New Icon')
@section('breadcrumb', 'Catalog & Taxonomy / Icons / New')

@section('body')

    <x-backend.page-header title="New Icon" subtitle="Add an icon to a library for services and catalog features." />

    <form method="POST" action="{{ route('admin.catalog.icons.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.catalog.icons._form', ['icon' => $icon, 'libraries' => $libraries])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.icons.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Icon</button>
        </div>
    </form>

@endsection
