@extends('backend.layouts.buyer')

@section('title', 'Edit Role: ' . $role->display_name)
@section('breadcrumb', 'Access Control / Roles / Edit')

@section('body')

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Role: {{ $role->display_name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Update role details and customize permission privileges for your institution.</p>
            </div>
            <a href="{{ route('buyer.roles.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3.5 py-2 bg-white border border-gray-300 rounded-xl">
                &larr; Back to Roles
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('buyer.roles.update', $role) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Role Details Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-base font-bold text-gray-900">Role Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role Name <span class="text-red-500">*</span></label>
                        <input type="text" name="display_name" value="{{ old('display_name', $role->display_name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Description</label>
                        <input type="text" name="description" value="{{ old('description', $role->description) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Permission Selection Matrix -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Permissions</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Check the permissions this role will grant to your team members.</p>
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        <input type="checkbox" id="master-select-all" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        Select All Permissions
                    </label>
                </div>

                <div class="space-y-6">
                    @foreach($permissionGroups as $groupName => $permissions)
                        <div class="bg-gray-50 rounded-xl border border-gray-200/80 p-5 group-box">
                            <div class="flex items-center justify-between mb-3.5 border-b border-gray-200 pb-2.5">
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    {{ $groupName ?: 'General' }}
                                    <span class="text-xs font-normal text-gray-500 font-mono">({{ $permissions->count() }})</span>
                                </h4>
                                <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 cursor-pointer">
                                    <input type="checkbox" class="group-select-all w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    Select Group
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($permissions as $perm)
                                    @php($isChecked = in_array($perm->name, old('permissions', $rolePermissions)))
                                    <label class="flex items-start gap-2.5 p-2.5 bg-white rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/20 cursor-pointer transition-colors">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ $isChecked ? 'checked' : '' }} class="perm-checkbox w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <div class="text-xs">
                                            <div class="font-semibold text-gray-800">{{ $perm->display_name ?: $perm->name }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono">{{ $perm->name }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('buyer.roles.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-semibold rounded-xl">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const masterCheckbox = document.getElementById('master-select-all');
            const allCheckboxes = document.querySelectorAll('.perm-checkbox');
            const groupSelectAlls = document.querySelectorAll('.group-select-all');

            masterCheckbox?.addEventListener('change', (e) => {
                allCheckboxes.forEach(cb => cb.checked = e.target.checked);
                groupSelectAlls.forEach(cb => cb.checked = e.target.checked);
            });

            document.querySelectorAll('.group-box').forEach(box => {
                const groupSelect = box.querySelector('.group-select-all');
                const groupBoxes = box.querySelectorAll('.perm-checkbox');

                groupSelect?.addEventListener('change', (e) => {
                    groupBoxes.forEach(cb => cb.checked = e.target.checked);
                });
            });
        });
    </script>

@endsection
