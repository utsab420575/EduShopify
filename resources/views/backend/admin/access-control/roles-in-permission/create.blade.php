@extends('backend.layouts.admin')

@section('title', 'Add Role in Permission')
@section('breadcrumb', 'Access Control / Add Role in Permission')

@section('body')

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add Role in Permission</h1>
                <p class="text-sm text-gray-500 mt-1">Select a role and assign default platform permissions.</p>
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

        <form method="POST" action="{{ route('admin.access-control.roles-in-permission.store') }}" class="space-y-6">
            @csrf

            <!-- Role Selector Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4 shadow-sm">
                <div class="max-w-md">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Roles Name <span class="text-red-500">*</span></label>
                    <select name="role_id" id="role-select" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Select Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->display_name ?? $r->name }} ({{ $r->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Permission Matrix Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-base font-bold text-gray-900">Permissions Matrix</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pick a role above, then toggle its permissions below.</p>
                </div>

                @php($oldRole = old('role_id') ? \App\Models\Role::find(old('role_id')) : null)
                <x-backend.permission-matrix
                    id="permission-matrix"
                    :groups="$permissionGroups"
                    :selected="old('permissions', [])"
                    :role-scope="$oldRole?->capability_scope"
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelect = document.getElementById('role-select');
            const matrixRoot = document.getElementById('permission-matrix');

            roleSelect?.addEventListener('change', async (e) => {
                const roleId = e.target.value;
                if (!roleId || !matrixRoot) return;

                try {
                    const res = await fetch(`/admin/access-control/roles-in-permission/${roleId}/json`);
                    if (res.ok) {
                        const data = await res.json();
                        const perms = new Set(data.permissions || []);

                        matrixRoot.querySelectorAll('.perm-matrix-checkbox').forEach(cb => {
                            cb.checked = perms.has(cb.value);
                        });

                        Alpine.$data(matrixRoot).updateCount();
                    }
                } catch(err) {
                    console.error(err);
                }
            });
        });
    </script>

@endsection
