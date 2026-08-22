@extends('backend.layouts.admin')

@section('title', 'New Role')
@section('breadcrumb', 'Access Control / Roles / New')

@section('body')

    <x-backend.page-header title="New Role" />

    <form method="POST" action="{{ route('admin.access-control.roles.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.access-control.roles._form', ['role' => $role, 'permissions' => $permissions])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.access-control.roles.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Role</button>
        </div>
    </form>

@endsection
