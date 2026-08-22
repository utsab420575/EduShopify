<x-backend.form-card title="Role Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if(!$role->exists)
            <x-backend.input name="name" label="Internal Name" required placeholder="e.g. content_moderator" hint="Lowercase letters, numbers, dashes and underscores only." />
            <x-backend.select name="capability_scope" label="Scope" required :selected="'platform'" :options="['platform' => 'Platform', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common']" />
        @endif
        <x-backend.input name="display_name" label="Display Name" required :value="$role->display_name" />
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$role->description" />
    </div>
</x-backend.form-card>

<x-backend.form-card title="Permissions">
    @php($currentPermissions = $role->exists ? $role->permissions->pluck('name')->all() : [])
    <div class="space-y-4">
        @foreach($permissions as $group => $groupPermissions)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group ?: 'General' }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($groupPermissions as $permission)
                        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-100 rounded-lg px-3 py-2 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $currentPermissions))) style="accent-color:var(--theme-primary)">
                            {{ $permission->display_name ?? $permission->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-backend.form-card>
