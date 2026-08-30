@php($existingCategories = $existingCategories ?? [])

<x-backend.form-card title="Category Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div x-data="{
            q: {{ Js::from(old('name', $category->name ?? '')) }},
            names: {{ Js::from(collect($existingCategories)->pluck('name')->values()) }},
            focused: false,
            get matches() {
                const v = this.q.trim().toLowerCase();
                return v.length > 1 ? this.names.filter(n => n.toLowerCase().includes(v)).slice(0, 8) : [];
            },
            get isDuplicate() {
                return this.matches.some(n => n.toLowerCase() === this.q.trim().toLowerCase());
            }
        }" x-effect="$dispatch('category-name-check', isDuplicate)">
            <div class="relative">
                <x-backend.input name="name" label="Name" required :value="$category->name" x-model="q"
                                  autocomplete="off" @focus="focused = true" @blur="focused = false" />

                <div x-show="focused && matches.length > 0" x-cloak
                     class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                    <div class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                        Existing categories matching "<span x-text="q"></span>"
                    </div>
                    <div class="max-h-40 overflow-y-auto divide-y divide-gray-50">
                        <template x-for="matchName in matches" :key="matchName">
                            <div class="px-3 py-2 text-xs flex items-center justify-between gap-2"
                                 :class="matchName.toLowerCase() === q.trim().toLowerCase() ? 'bg-red-50 text-red-700 font-semibold' : 'text-gray-700'">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <i class="fa-solid fa-folder text-gray-300 text-[10px] shrink-0"></i>
                                    <span class="truncate" x-text="matchName"></span>
                                </span>
                                <span x-show="matchName.toLowerCase() === q.trim().toLowerCase()" class="text-[9px] font-bold uppercase text-red-600 shrink-0">Exact match</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <template x-if="isDuplicate">
                <p class="mt-1 text-xs text-red-600 font-medium">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    A category named "<span x-text="q"></span>" already exists — choose a different name to avoid duplicates.
                </p>
            </template>
        </div>
        <x-backend.select name="parent_id" label="Parent Category" placeholder="None (root category)" :selected="$category->parent_id">
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </x-backend.select>
        <x-backend.select name="type" label="Type" required :selected="$category->type ?: 'both'" :options="['product' => 'Product', 'service' => 'Service', 'both' => 'Both']" />
        <div class="flex items-center gap-2 pt-7">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true)) style="accent-color:var(--theme-primary)">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$category->description" />
    </div>
</x-backend.form-card>
