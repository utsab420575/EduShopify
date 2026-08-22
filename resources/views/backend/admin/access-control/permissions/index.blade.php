@extends('backend.layouts.admin')

@section('title', 'Permissions')
@section('breadcrumb', 'Access Control / Permissions')

@section('body')

    <x-backend.page-header title="Permissions" subtitle="The full permission catalogue. Edit display names or restrict availability." />

    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('admin.access-control.permissions.index') }}" class="text-sm font-medium px-3 py-1.5 rounded-lg {{ !$scope ? 'text-white' : 'border border-gray-300 text-gray-700' }}" @if(!$scope) style="background:var(--theme-primary)" @endif>All</a>
        @foreach(['platform' => 'Platform', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common'] as $value => $label)
            <a href="{{ route('admin.access-control.permissions.index', ['scope' => $value]) }}" class="text-sm font-medium px-3 py-1.5 rounded-lg {{ $scope === $value ? 'text-white' : 'border border-gray-300 text-gray-700' }}" @if($scope === $value) style="background:var(--theme-primary)" @endif>{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-6">
        @foreach($permissions as $group => $groupPermissions)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $group ?: 'General' }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($groupPermissions as $permission)
                        <form method="POST" action="{{ route('admin.access-control.permissions.update', $permission) }}" class="flex flex-wrap items-center gap-3 px-5 py-3">
                            @csrf @method('PUT')
                            <div class="flex-1 min-w-[200px]">
                                <input type="text" name="display_name" value="{{ $permission->display_name ?? $permission->name }}" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-1.5">
                                <p class="text-xs text-gray-400 mt-0.5">{{ $permission->name }} @if($permission->is_sensitive)<span class="text-amber-500 font-medium ml-1">Sensitive</span>@endif</p>
                            </div>
                            <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                <input type="checkbox" name="is_active" value="1" @checked($permission->is_active) style="accent-color:var(--theme-primary)">
                                Active
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                <input type="checkbox" name="is_assignable" value="1" @checked($permission->is_assignable) style="accent-color:var(--theme-primary)">
                                Assignable
                            </label>
                            <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Save</button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

@endsection
