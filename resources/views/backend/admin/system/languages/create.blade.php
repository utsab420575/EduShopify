@extends('backend.layouts.admin')

@section('title', 'New Language')
@section('breadcrumb', 'System & Settings / Languages / New')

@section('body')

    <x-backend.page-header title="New Language" />

    <form method="POST" action="{{ route('admin.system.languages.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.system.languages._form', ['language' => $language])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.system.languages.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Language</button>
        </div>
    </form>

@endsection
