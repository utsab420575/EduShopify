@extends('backend.layouts.supplier')

@section('title', 'Roles & Permissions')
@section('breadcrumb', 'Access Control / Roles')

@section('body')

    <x-backend.page-header title="Roles &amp; Permissions" subtitle="Assign organizational roles and inspect permission scopes." />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($roles as $role)
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">{{ $role->name }}</h3>
                    <span class="text-xs text-gray-400 font-mono">{{ $role->permissions->count() }} permissions</span>
                </div>
                <p class="text-xs text-gray-500">{{ $role->description ?? 'Role for supplier account access.' }}</p>
                <div class="pt-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('supplier.roles.show', $role) }}" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg">
                        Manage Members &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

@endsection
