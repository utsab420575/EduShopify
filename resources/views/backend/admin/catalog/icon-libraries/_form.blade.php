<x-backend.form-card title="Icon Library Details">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Library Name" required :value="$library->name" placeholder="e.g. FontAwesome 6" />
        <x-backend.input name="slug" label="Slug / Identifier" required :value="$library->slug" placeholder="e.g. fontawesome" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <x-backend.select name="type" label="Library Type" required :selected="$library->type ?: 'Local'" :options="['Local' => 'Local (Bundled)', 'CDN' => 'CDN Hosted', 'Custom' => 'Custom SVG / Collection']" />
        <x-backend.input name="cdn_url" label="CDN URL (Optional)" :value="$library->cdn_url" placeholder="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    </div>

    <div class="flex items-center gap-2 mt-5 pt-3 border-t border-gray-100">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $library->exists ? $library->is_active : true)) style="accent-color:var(--theme-primary)" class="rounded border-gray-300 w-4 h-4">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active (Icons in this library can be used in services and catalog)</label>
    </div>
</x-backend.form-card>
