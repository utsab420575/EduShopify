@extends('backend.layouts.supplier')

@section('title', 'Role: ' . ($role->display_name ?: $role->name))
@section('breadcrumb', 'Access Control / Roles / ' . ($role->display_name ?: $role->name))

@section('body')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $role->display_name ?: $role->name }}</h1>
                @if($role->is_system || $role->account_id === null)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        Platform System Role
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Custom Company Role
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $role->description ?: 'Role permissions and member assignments for this organization.' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('supplier.roles.duplicate', $role) }}" onsubmit="const name = prompt('Name for the duplicated role:', '{{ addslashes($role->display_name ?: $role->name) }} (Copy)'); if (name) { this.querySelector('input[name=new_display_name]').value = name; return true; } return false;">
                @csrf
                <input type="hidden" name="new_display_name" value="">
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Duplicate Role
                </button>
            </form>

            @if(!$role->is_system && $role->account_id === $account->id)
                <a href="{{ route('supplier.roles.edit', $role) }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Permissions
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Member Assignment Card -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">Assigned Team Members</h3>
                <p class="text-xs text-gray-500 mb-4">Assign or remove this role for active members in your organization.</p>

                <div class="divide-y divide-gray-100">
                    @forelse($members as $m)
                        @php($assigned = $assignedUserIds->contains($m->user_id))
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="font-semibold text-sm text-gray-900">{{ $m->user?->name }}</div>
                                <div class="text-xs text-gray-400">{{ $m->user?->email }}</div>
                            </div>
                            @if($assigned)
                                <form method="POST" action="{{ route('supplier.roles.unassign', $role) }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $m->id }}">
                                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                        Unassign
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('supplier.roles.assign', $role) }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $m->id }}">
                                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Assign Role
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 py-4 text-center">No active organization members found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Categorized Permission Breakdown -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Included Permissions ({{ $role->permissions->count() }})</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Permissions granted to members holding this role.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($groupedPermissions as $groupName => $perms)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-2.5">{{ $groupName ?: 'General' }} ({{ $perms->count() }})</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($perms as $perm)
                                    <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-gray-200 text-xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span class="font-medium text-gray-800">{{ $perm->display_name ?: $perm->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 py-4 text-center">No permissions attached to this role.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

@endsection
