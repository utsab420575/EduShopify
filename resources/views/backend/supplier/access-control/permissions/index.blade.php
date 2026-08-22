@extends('backend.layouts.supplier')

@section('title', 'Platform Permissions')
@section('breadcrumb', 'Access Control / Permissions')

@section('body')

    <x-backend.page-header title="System Permissions" subtitle="Catalog of available permissions grouped by functional domain." />

    <div class="space-y-6">
        @foreach($permissionGroups as $group => $perms)
            <x-backend.form-card title="{{ ucfirst($group ?: 'General') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                    @foreach($perms as $p)
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="font-bold text-gray-900 font-mono text-[11px]">{{ $p->name }}</p>
                            <p class="text-gray-500 text-[10px] mt-0.5">{{ $p->description ?? 'Supplier capability permission' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>
        @endforeach
    </div>

@endsection
