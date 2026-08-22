<x-backend.form-card title="Buyer Type Details">
    <x-backend.input name="name" label="Name" required :value="$buyerType->name" />
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$buyerType->description" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $buyerType->exists ? $buyerType->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
