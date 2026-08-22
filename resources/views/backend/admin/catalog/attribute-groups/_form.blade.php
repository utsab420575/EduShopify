<x-backend.form-card title="Attribute Group Details" description="Group name and display ordering for specification sections.">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Group Name" required :value="old('name', $group->name)" placeholder="e.g. Main Features, Connectivity, Physical Spec" />
        <x-backend.input name="slug" label="Slug (optional)" :value="old('slug', $group->slug)" placeholder="auto-generated-from-name" />
    </div>

    <div class="mt-4">
        <x-backend.textarea name="description" label="Description (optional)" :rows="3" :value="old('description', $group->description)" placeholder="Brief description of specifications contained in this group..." />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <x-backend.input type="number" name="sort_order" label="Sort Order" :value="old('sort_order', $group->sort_order ?? 0)" min="0" placeholder="0" />
        <div class="flex items-center gap-2 pt-6">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $group->exists ? $group->is_active : true)) style="accent-color:var(--theme-primary)">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active / Visible in Catalog</label>
        </div>
    </div>
</x-backend.form-card>
