<x-backend.form-card title="Unit Details">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-backend.input name="name" label="Name" required :value="$unit->name" placeholder="e.g. Kilogram" />
        <x-backend.input name="symbol" label="Symbol" required :value="$unit->symbol" placeholder="e.g. kg" />
        <x-backend.select name="unit_type" label="Type" required :selected="$unit->unit_type ?: 'count'" :options="['count' => 'Count', 'weight' => 'Weight', 'volume' => 'Volume', 'length' => 'Length', 'area' => 'Area', 'time' => 'Time', 'other' => 'Other']" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $unit->exists ? $unit->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
