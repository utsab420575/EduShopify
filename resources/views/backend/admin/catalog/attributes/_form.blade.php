@php
    $inputTypes = $inputTypes ?? \App\Models\InputType::active()->ordered()->get();
    $selectedInputType = old('input_type', $attribute->input_type ?: 'text');
    $selectedInputTypeId = old('input_type_id', $attribute->input_type_id ?: ($inputTypes->where('code', $selectedInputType)->first()?->id ?? 1));

    $existingValues = old('values');
    if ($existingValues === null && $attribute->exists) {
        $existingValues = $attribute->values->map(fn($v) => [
            'id' => $v->id,
            'value' => $v->value,
            'slug' => $v->slug,
            'color_hex' => $v->color_hex,
            'sort_order' => $v->sort_order,
            'is_active' => $v->is_active,
        ])->toArray();
    }
    $existingValues = $existingValues ?: [];
    $validationRules = (array) ($attribute->validation_rules ?? []);
@endphp

<div x-data="{
    inputTypes: {{ Js::from($inputTypes->map(fn($t) => ['id' => $t->id, 'code' => $t->code, 'name' => $t->name, 'has_options' => (bool)$t->has_options, 'is_multiple' => (bool)$t->is_multiple])) }},
    inputTypeId: {{ $selectedInputTypeId }},
    get currentInputType() {
        return this.inputTypes.find(t => t.id == this.inputTypeId) || this.inputTypes[0] || {};
    },
    get inputType() {
        return this.currentInputType.code || 'text';
    },
    get hasOptions() {
        return !!this.currentInputType.has_options;
    },
    values: {{ Js::from($existingValues) }},
    addValue() {
        this.values.push({
            id: null,
            value: '',
            slug: '',
            color_hex: '#000000',
            sort_order: this.values.length,
            is_active: true
        });
    },
    removeValue(index) {
        this.values.splice(index, 1);
    },
    autoSlug(index) {
        if (!this.values[index].slug || this.values[index].slug === '') {
            this.values[index].slug = this.values[index].value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        }
    }
}" class="space-y-6">

    {{-- Main Attribute Details --}}
    <x-backend.form-card title="Attribute Details" description="Define the specification name, grouping, input format, and measurement unit.">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-backend.input name="name" label="Attribute Name" required :value="old('name', $attribute->name)" placeholder="e.g. Bluetooth Version, Amplifier Power, Battery Capacity" />
            <x-backend.input name="slug" label="Slug (optional)" :value="old('slug', $attribute->slug)" placeholder="auto-generated-from-name" />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Attribute Group (Specification Section)</label>
                <select name="attribute_group_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— No Group (Unassigned) —</option>
                    @foreach($attributeGroups as $group)
                        <option value="{{ $group->id }}" @selected(old('attribute_group_id', $attribute->attribute_group_id) == $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Organizes this field under a section heading (e.g. Main Features, Connectivity) on the product page.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Input Format / Type <span class="text-red-500">*</span></label>
                <select name="input_type_id" x-model="inputTypeId" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($inputTypes as $t)
                        <option value="{{ $t->id }}" @selected($selectedInputTypeId == $t->id)>
                            {{ $t->name }} {{ $t->has_options ? '(Predefined options)' : '' }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="input_type" :value="inputType">
                <p class="text-xs text-gray-400 mt-1" x-text="currentInputType.description || ''"></p>
            </div>

            <x-backend.select name="unit_id" label="Measurement Unit (optional)" placeholder="— No Unit —" :selected="old('unit_id', $attribute->unit_id)">
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id', $attribute->unit_id) == $unit->id)>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </x-backend.select>

            <x-backend.input name="placeholder" label="Input Placeholder / Hint" :value="old('placeholder', $attribute->placeholder)" placeholder="e.g. e.g. 5.4 or Enter wattage" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <x-backend.input type="number" name="sort_order" label="Default Sort Order" :value="old('sort_order', $attribute->sort_order ?? 0)" min="0" placeholder="0" />
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $attribute->exists ? $attribute->is_active : true)) style="accent-color:var(--theme-primary)">
                <label for="is_active" class="text-sm font-medium text-gray-700">Active in Catalog</label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100">
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $attribute->is_filterable)) style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Filterable by Default</span>
                    <span class="text-[11px] text-gray-400">Sidebar search filter</span>
                </div>
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_variant" value="1" @checked(old('is_variant', $attribute->is_variant)) style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Variant Attribute</span>
                    <span class="text-[11px] text-gray-400">Creates product variants</span>
                </div>
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $attribute->is_required)) style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Required by Default</span>
                    <span class="text-[11px] text-gray-400">Mandatory for suppliers</span>
                </div>
            </label>
            <label x-show="hasOptions" class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="allow_custom_value" value="1" @checked(old('allow_custom_value', $attribute->allow_custom_value)) style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Allow "Other"</span>
                    <span class="text-[11px] text-gray-400">Supplier can type custom value</span>
                </div>
            </label>
        </div>
    </x-backend.form-card>

    {{-- Validation Rules Configuration Card --}}
    <x-backend.form-card title="Input Validation Rules" description="Configure input boundaries for suppliers.">
        {{-- Number rules --}}
        <div x-show="inputType === 'number'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-backend.input type="number" step="any" name="min_value" label="Minimum Value (optional)" :value="old('min_value', $validationRules['min'] ?? '')" placeholder="e.g. 0" />
            <x-backend.input type="number" step="any" name="max_value" label="Maximum Value (optional)" :value="old('max_value', $validationRules['max'] ?? '')" placeholder="e.g. 5000" />
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="decimal_allowed" id="decimal_allowed" value="1" @checked(old('decimal_allowed', !empty($validationRules['decimal']))) style="accent-color:var(--theme-primary)">
                <label for="decimal_allowed" class="text-sm font-medium text-gray-700">Allow Decimal Numbers</label>
            </div>
        </div>

        {{-- Text rules --}}
        <div x-show="inputType === 'text' || inputType === 'textarea'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-backend.input type="number" name="min_length" label="Minimum Length (characters)" :value="old('min_length', $validationRules['min_length'] ?? '')" placeholder="e.g. 2" />
            <x-backend.input type="number" name="max_length" label="Maximum Length (characters)" :value="old('max_length', $validationRules['max_length'] ?? '')" placeholder="e.g. 255" />
        </div>

        <div x-show="!hasOptions && inputType !== 'number' && inputType !== 'text' && inputType !== 'textarea'" class="text-xs text-gray-400 py-2">
            Standard format validation will be applied.
        </div>

        <div x-show="hasOptions" class="text-xs text-gray-400 py-2">
            Values are validated against the predefined choices below (or the custom value if allowed).
        </div>
    </x-backend.form-card>

    {{-- Predefined Attribute Values Card (For Input Types that have options) --}}
    <div x-show="hasOptions" class="space-y-4">
        <x-backend.form-card title="Predefined Attribute Values" description="Add selectable options for this attribute. Suppliers will choose from these values.">
            <div class="space-y-3">
                <template x-for="(val, index) in values" :key="index">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                        {{-- Drag / Order handle --}}
                        <div class="text-gray-400 text-xs font-semibold px-1" x-text="index + 1"></div>

                        {{-- Hidden ID if existing --}}
                        <input type="hidden" :name="'values[' + index + '][id]'" :value="val.id">

                        {{-- Value Name --}}
                        <div class="flex-1 min-w-[150px]">
                            <input type="text" :name="'values[' + index + '][value]'" x-model="val.value" @input="autoSlug(index)"
                                   placeholder="Value (e.g. Bluetooth 5.4, 16GB, Black)" required
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Slug --}}
                        <div class="flex-1 min-w-[120px]">
                            <input type="text" :name="'values[' + index + '][slug]'" x-model="val.slug"
                                   placeholder="Slug"
                                   class="w-full text-xs font-mono rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-600 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Color Hex picker (if color input type) --}}
                        <template x-if="inputType === 'color'">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <input type="color" x-model="val.color_hex" class="w-8 h-8 rounded border border-gray-300 cursor-pointer p-0.5 bg-white">
                                <input type="text" :name="'values[' + index + '][color_hex]'" x-model="val.color_hex"
                                       placeholder="#000000" class="w-20 text-xs font-mono rounded-lg border border-gray-300 px-2 py-2 bg-white">
                            </div>
                        </template>

                        {{-- Sort Order --}}
                        <div class="w-20 shrink-0">
                            <input type="number" :name="'values[' + index + '][sort_order]'" x-model="val.sort_order"
                                   placeholder="Order" class="w-full text-xs rounded-lg border border-gray-300 px-2 py-2 text-center bg-white">
                        </div>

                        {{-- Remove Button --}}
                        <button type="button" @click="removeValue(index)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-100/60 transition shrink-0" title="Remove value">
                            <i class="fa-regular fa-trash-can text-sm"></i>
                        </button>
                    </div>
                </template>

                <div x-show="values.length === 0" class="border border-dashed border-gray-300 rounded-xl p-6 text-center text-xs text-gray-500">
                    No predefined values added yet. Click <strong>"Add Value"</strong> below to create options.
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="addValue()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition border border-indigo-200">
                        <i class="fa-solid fa-plus text-xs"></i> Add Value
                    </button>
                    <span class="text-xs text-gray-400" x-text="values.length + ' option(s) defined'"></span>
                </div>
            </div>
        </x-backend.form-card>
    </div>

</div>
