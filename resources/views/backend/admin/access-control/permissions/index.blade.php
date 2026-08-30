@extends('backend.layouts.admin')

@section('title', 'Permissions')
@section('breadcrumb', 'Access Control / Permissions')

@section('body')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">The full permission catalogue. Edit display names or restrict availability.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.access-control.route-permissions.index') }}" class="btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-route"></i> Route Discovery
            </a>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.access-control.permissions.index') }}" class="text-sm font-medium px-3 py-1.5 rounded-lg {{ !$scope ? 'btn-primary' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">All</a>
            @foreach(['platform' => 'Platform', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common'] as $value => $label)
                <a href="{{ route('admin.access-control.permissions.index', ['scope' => $value]) }}" class="text-sm font-medium px-3 py-1.5 rounded-lg {{ $scope === $value ? 'btn-primary' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="relative flex-1 min-w-[200px] sm:max-w-xs" x-data="{ search: '' }">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" @input="
                document.querySelectorAll('[data-perm-row]').forEach(row => {
                    row.style.display = row.dataset.permRow.toLowerCase().includes(search.toLowerCase()) ? '' : 'none';
                });
                document.querySelectorAll('[data-perm-group]').forEach(group => {
                    const anyVisible = Array.from(group.querySelectorAll('[data-perm-row]')).some(r => r.style.display !== 'none');
                    group.style.display = anyVisible ? '' : 'none';
                });
            " placeholder="Search permissions by name…" class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
        </div>
    </div>

    <div class="space-y-6">
        @foreach($permissions as $group => $groupPermissions)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" data-perm-group>
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $group ?: 'General' }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($groupPermissions as $permission)
                        <form method="POST" action="{{ route('admin.access-control.permissions.update', $permission) }}" class="flex flex-wrap items-center gap-3 px-5 py-3" data-perm-row="{{ $permission->name }} {{ $permission->display_name }}">
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
