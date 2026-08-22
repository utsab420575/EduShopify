<x-backend.form-card title="Language Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="code" label="Code" required :value="$language->code" placeholder="en" />
        <x-backend.input name="name" label="Name" required :value="$language->name" placeholder="English" />
        <x-backend.input name="native_name" label="Native Name" :value="$language->native_name" />
        <x-backend.select name="direction" label="Direction" required :selected="$language->direction ?: 'ltr'" :options="['ltr' => 'Left to Right', 'rtl' => 'Right to Left']" />
        <x-backend.input name="sort_order" label="Sort Order" type="number" :value="$language->sort_order ?? 0" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $language->exists ? $language->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
