<x-backend.form-card title="Brand Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Name" required :value="$brand->name" />
        <x-backend.input name="website" label="Website" type="url" :value="$brand->website" placeholder="https://" />
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$brand->description" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $brand->exists ? $brand->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
