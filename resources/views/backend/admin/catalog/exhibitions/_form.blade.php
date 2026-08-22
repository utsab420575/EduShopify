<x-backend.form-card title="Exhibition Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Name" required :value="$exhibition->name" />
        <x-backend.input name="website" label="Website" type="url" :value="$exhibition->website" placeholder="https://" />
        <x-backend.input name="starts_at" label="Start Date" type="date" :value="$exhibition->starts_at?->format('Y-m-d')" />
        <x-backend.input name="ends_at" label="End Date" type="date" :value="$exhibition->ends_at?->format('Y-m-d')" />
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$exhibition->description" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $exhibition->exists ? $exhibition->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
