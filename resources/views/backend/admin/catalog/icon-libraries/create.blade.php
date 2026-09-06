@extends('backend.layouts.admin')

@section('title', 'New Icon Library')
@section('breadcrumb', 'Catalog & Taxonomy / Icon Libraries / New')

@section('body')

    <x-backend.page-header title="New Icon Library" subtitle="Register a new icon pack or CDN library for platform-wide use." />

    <form method="POST" action="{{ route('admin.catalog.icon-libraries.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.catalog.icon-libraries._form', ['library' => $library])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.icon-libraries.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Library</button>
        </div>
    </form>

@endsection
