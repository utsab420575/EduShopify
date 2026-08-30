<x-backend.form-card title="Input Type Details" description="Define input format, options behavior, and multi-selection support.">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Display Name" required :value="old('name', $inputType->name)" placeholder="e.g. Single Select (Dropdown)" />
        <x-backend.input name="code" label="Type Code (unique identifier)" required :value="old('code', $inputType->code)" placeholder="e.g. select, multi_select, date" />
    </div>

    <div class="mt-4">
        <x-backend.input name="description" label="Description" :value="old('description', $inputType->description)" placeholder="e.g. Choose one option from a predefined list (e.g. Material, Storage)" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-100">
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="has_options" value="1" @checked(old('has_options', $inputType->has_options)) style="accent-color:var(--theme-primary)">
            <div>
                <span class="font-semibold block text-xs text-gray-900">Has Predefined Options</span>
                <span class="text-[11px] text-gray-400">Uses attribute_values table for choices</span>
            </div>
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="is_multiple" value="1" @checked(old('is_multiple', $inputType->is_multiple)) style="accent-color:var(--theme-primary)">
            <div>
                <span class="font-semibold block text-xs text-gray-900">Allows Multiple Values</span>
                <span class="text-[11px] text-gray-400">Supplier can pick more than 1 choice</span>
            </div>
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $inputType->exists ? $inputType->is_active : true)) style="accent-color:var(--theme-primary)">
            <div>
                <span class="font-semibold block text-xs text-gray-900">Active</span>
                <span class="text-[11px] text-gray-400">Available for new attributes</span>
            </div>
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <x-backend.input type="number" name="sort_order" label="Sort Order" :value="old('sort_order', $inputType->sort_order ?? 0)" min="0" placeholder="0" />
    </div>
</x-backend.form-card>
