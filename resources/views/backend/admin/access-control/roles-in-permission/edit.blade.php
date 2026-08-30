@extends('backend.layouts.admin')

@section('title', 'Edit Role in Permission: ' . ($role->display_name ?? $role->name))
@section('breadcrumb', 'Access Control / Edit Role in Permission')

@section('body')

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Role in Permission: {{ $role->display_name ?? $role->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Configure default permissions mapped to this role.</p>
            </div>
            <a href="{{ route('admin.access-control.roles-in-permission.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3.5 py-2 bg-white border border-gray-300 rounded-xl">
                &larr; Back to Overview
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.access-control.roles-in-permission.update', $role) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Role Summary Box -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role Name</div>
                    <div class="text-lg font-bold text-gray-900">{{ $role->display_name ?? $role->name }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ $role->name }} &bull; {{ ucfirst($role->capability_scope) }}</div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                        {{ $role->permissions->count() }} active permissions
                    </span>
                </div>
            </div>

            <!-- Permission Matrix Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-base font-bold text-gray-900">Permissions Matrix</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Toggle permissions below for this role.</p>
                </div>

                <x-backend.permission-matrix
                    :groups="$permissionGroups"
                    :selected="old('permissions', $rolePermissions)"
                    :role-scope="$role->capability_scope"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('admin.access-control.roles-in-permission.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-7 py-2.5 text-sm font-semibold rounded-xl">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

@endsection
