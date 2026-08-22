<x-backend.form-card title="Document Type Details">
    <x-backend.input name="name" label="Name" required :value="$documentType->name" />
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$documentType->description" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <x-backend.input name="accepted_formats" label="Accepted Formats" hint="Comma-separated extensions, e.g. pdf, jpg, png" :value="old('accepted_formats', collect($documentType->accepted_formats)->implode(', '))" />
        <x-backend.input name="max_size_kb" label="Max Size (KB)" type="number" :value="$documentType->max_size_kb" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $documentType->exists ? $documentType->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
