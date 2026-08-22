@extends('backend.layouts.buyer')

@section('title', 'Permissions')
@section('breadcrumb', 'Organization / Permissions')

@section('body')

    <x-backend.page-header title="Permissions" subtitle="Permissions available to compose into custom roles." />

    <div class="space-y-6">
        @foreach($permissionGroups as $group => $permissions)
            <x-backend.form-card :title="$group ?: 'General'">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($permissions as $permission)
                        <div class="flex items-center justify-between border border-gray-100 rounded-lg px-3 py-2">
                            <span class="text-sm text-gray-700">{{ $permission->display_name ?? $permission->name }}</span>
                            @if($permission->is_sensitive)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Sensitive</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>
        @endforeach
    </div>

@endsection
