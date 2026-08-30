@extends('backend.layouts.buyer')

@section('title', 'Roles & Permissions')
@section('breadcrumb', 'Access Control / Roles')

@section('body')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Roles &amp; Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Manage global system roles and custom procurement roles for your institution.</p>
        </div>
        <a href="{{ route('buyer.roles.create') }}" class="btn-primary text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Custom Role
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($roles as $role)
            @php($isSystem = $role->is_system || $role->account_id === null)
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $role->display_name ?: $role->name }}</h3>
                            </div>
                            <span class="text-xs text-gray-400 font-mono">{{ $role->name }}</span>
                        </div>
                        @if($isSystem)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                System Role
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                Custom Institution
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-600 line-clamp-2 min-h-[32px]">
                        {{ $role->description ?: 'Standard role permissions for buyer account access.' }}
                    </p>

                    <div class="flex items-center gap-3 pt-2 text-xs text-gray-500 border-t border-gray-100">
                        <span class="font-medium text-gray-700">🔒 {{ $role->permissions->count() }} permissions</span>
                        <span>•</span>
                        <span class="font-medium text-gray-700">👥 {{ $role->users_count ?? 0 }} assigned</span>
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1">
                        <!-- Duplicate Role Form -->
                        <form method="POST" action="{{ route('buyer.roles.duplicate', $role) }}" onsubmit="const name = prompt('Name for the duplicated role:', '{{ addslashes($role->display_name ?: $role->name) }} (Copy)'); if (name) { this.querySelector('input[name=new_display_name]').value = name; return true; } return false;" class="inline">
                            @csrf
                            <input type="hidden" name="new_display_name" value="">
                            <button type="submit" title="Duplicate this role" class="text-gray-500 hover:text-amber-600 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </form>

                        @if(!$isSystem)
                            <a href="{{ route('buyer.roles.edit', $role) }}" title="Edit Custom Role" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('buyer.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete this custom role?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete Role" class="text-gray-500 hover:text-red-600 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>

                    <a href="{{ route('buyer.roles.show', $role) }}" class="btn-primary text-xs font-semibold px-3.5 py-1.5 rounded-lg flex items-center gap-1.5">
                        Manage &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

@endsection
