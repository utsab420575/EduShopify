<x-backend.form-card title="Attribute Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Name" required :value="$attribute->name" />
        <x-backend.select name="input_type" label="Input Type" required :selected="$attribute->input_type ?: 'text'" :options="[
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'select' => 'Select (single choice)',
            'multi_select' => 'Select (multiple choices)',
            'boolean' => 'Yes / No',
            'date' => 'Date',
            'color' => 'Color',
        ]" />
        <x-backend.select name="unit_id" label="Unit" placeholder="No unit" :selected="$attribute->unit_id">
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $attribute->unit_id) == $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>
            @endforeach
        </x-backend.select>
        <x-backend.input name="placeholder" label="Placeholder" :value="$attribute->placeholder" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer">
            <input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $attribute->is_filterable)) style="accent-color:var(--theme-primary)">
            Filterable
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer">
            <input type="checkbox" name="is_variant" value="1" @checked(old('is_variant', $attribute->is_variant)) style="accent-color:var(--theme-primary)">
            Variant Attribute
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer">
            <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $attribute->is_required)) style="accent-color:var(--theme-primary)">
            Required
        </label>
    </div>

    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $attribute->exists ? $attribute->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>

@if(in_array($attribute->input_type ?? 'text', ['select', 'multi_select', 'color']) || old('values') || ($attribute->exists && $attribute->values->isNotEmpty()))
    <x-backend.form-card title="Values" description="One value per line. Existing values not listed here will be removed.">
        <x-backend.textarea name="values" :rows="6" :value="old('values', $attribute->exists ? $attribute->values->pluck('value')->implode(PHP_EOL) : '')" />
    </x-backend.form-card>
@else
    <x-backend.form-card title="Values" description="Only applicable for Select / Multi-select / Color attributes. One value per line.">
        <x-backend.textarea name="values" :rows="4" :value="old('values')" />
    </x-backend.form-card>
@endif
