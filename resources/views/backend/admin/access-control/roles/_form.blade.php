<x-backend.form-card title="Role Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if(!$role->exists)
            <x-backend.input name="name" label="Internal Technical Slug" required placeholder="e.g. content_moderator" hint="Lowercase letters, numbers, dashes and underscores only." />
            <x-backend.select name="capability_scope" label="Scope" required :selected="'platform'" :options="['platform' => 'Platform Staff', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common', 'both' => 'Both (Supplier & Buyer)']" />
        @else
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Technical Slug</label>
                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono text-gray-700">{{ $role->name }}</div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Scope</label>
                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold capitalize text-gray-700">{{ $role->capability_scope }}</div>
            </div>
        @endif
        <div class="sm:col-span-2">
            <x-backend.input name="display_name" label="Display Name" required :value="$role->display_name" />
        </div>
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$role->description" />
    </div>
</x-backend.form-card>

<x-backend.form-card title="Assign Default Permissions" description="Configure default permissions mapped to this role. When a supplier or buyer assigns this role, the user inherits all checked permissions.">
    @php($currentPermissions = $role->exists ? $role->permissions->pluck('name')->all() : [])
    <x-backend.permission-matrix
        :groups="$permissions"
        :selected="old('permissions', $currentPermissions)"
        :role-scope="old('capability_scope', $role->capability_scope)"
    />
</x-backend.form-card>
