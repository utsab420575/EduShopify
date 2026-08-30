@extends('backend.layouts.admin')

@section('title', $category->name . ' — Specifications / Attributes')
@section('breadcrumb', 'Catalog & Taxonomy / Categories / ' . $category->name . ' / Specifications')

@section('body')

    {{-- Top Navigation Tabs & Page Header --}}
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $totalAssignedCount }} {{ Str::plural('Specification', $totalAssignedCount) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Configure specification attributes and sections for products created under this category.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 bg-white">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Category Settings
                </a>
                <button type="button" @click="$dispatch('open-group-modal')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 bg-white">
                    <i class="fa-solid fa-folder-plus text-xs"></i> New Group
                </button>
                <button type="button" @click="$dispatch('open-bulk-modal')" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition">
                    <i class="fa-solid fa-layer-group text-xs"></i> Bulk Assign by Group
                </button>
                <button type="button" @click="$dispatch('open-add-modal')" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus text-xs"></i> Add Attribute
                </button>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-6 -mb-px">
                <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    General Information
                </a>
                <a href="{{ route('admin.catalog.categories.attributes.index', $category) }}" class="py-3 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2">
                    <span>Specifications &amp; Attributes</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800 font-semibold">{{ $totalAssignedCount }}</span>
                </a>
            </nav>
        </div>
    </div>

    {{-- Grouped Attribute Tables --}}
    @if($groupedAttributes->isEmpty())
        <div class="fe-card rounded-2xl p-12 text-center border border-dashed border-gray-300 bg-white">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-sliders text-2xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900">No specifications configured yet</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mt-1 mb-5">
                Assign attributes (like <em>Bluetooth Version</em>, <em>Battery Capacity</em>, <em>Amplifier Power</em>) to this category so suppliers will receive standard technical specification fields.
            </p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" @click="$dispatch('open-bulk-modal')" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-layer-group text-xs"></i> Bulk Assign by Group
                </button>
                <button type="button" @click="$dispatch('open-add-modal')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i> Add Individual Attribute
                </button>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($groupedAttributes as $groupData)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    {{-- Group Header --}}
                    <div class="flex items-center justify-between px-5 py-3.5 bg-gray-50/80 border-b border-gray-200">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-layer-group text-xs"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ $groupData['group_name'] }}</h3>
                                @if($groupData['group']?->description)
                                    <p class="text-[11px] text-gray-500">{{ $groupData['group']->description }}</p>
                                @endif
                            </div>
                        </div>

                        <span class="text-xs font-medium text-gray-500 bg-white px-2.5 py-1 rounded-full border border-gray-200">
                            {{ $groupData['attributes']->count() }} {{ Str::plural('field', $groupData['attributes']->count()) }}
                        </span>
                    </div>

                    {{-- Table of Attributes in this Group --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/40 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-5 py-3">Attribute Name</th>
                                    <th class="px-4 py-3">Input Type</th>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3 text-center">Required</th>
                                    <th class="px-4 py-3 text-center">Filterable</th>
                                    <th class="px-4 py-3 text-center">Variant</th>
                                    <th class="px-4 py-3 text-center">Sort Order</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($groupData['attributes'] as $attr)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-5 py-3.5">
                                            <div class="font-semibold text-gray-900 text-sm">{{ $attr->name }}</div>
                                            <div class="text-[11px] text-gray-400 font-mono">{{ $attr->slug }}</div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 font-semibold uppercase text-[10px]">
                                                {{ str_replace('_', ' ', $attr->input_type) }}
                                            </span>
                                            @if(in_array($attr->input_type, ['select', 'multi_select', 'color']))
                                                <span class="text-[10px] text-gray-400 block mt-0.5">
                                                    {{ $attr->values->count() }} options
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-600 font-medium">
                                            {{ $attr->unit ? ($attr->unit->name . ' (' . $attr->unit->symbol . ')') : '—' }}
                                        </td>

                                        <td class="px-4 py-3.5 text-center">
                                            @if($attr->pivot->is_required)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">Yes</span>
                                            @else
                                                <span class="text-gray-400 text-xs">No</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if($attr->pivot->is_filterable)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Yes</span>
                                            @else
                                                <span class="text-gray-400 text-xs">No</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if($attr->pivot->is_variant)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">Yes</span>
                                            @else
                                                <span class="text-gray-400 text-xs">No</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center text-xs font-mono text-gray-700">
                                            {{ $attr->pivot->sort_order }}
                                        </td>

                                        <td class="px-5 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="$dispatch('open-modal-edit-attr-{{ $attr->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit Specification Settings">
                                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.catalog.categories.attributes.destroy', [$category, $attr]) }}" onsubmit="return confirm('Remove {{ $attr->name }} from {{ $category->name }}?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50" title="Remove from Category">
                                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- EDIT MODALS — one per assigned attribute, opened by the row's pencil icon --}}
    {{-- ========================================================================= --}}
    @foreach($assignedAttributes as $attr)
        <x-backend.modal :id="'edit-attr-'.$attr->id" title="Edit Specification Settings">
            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs space-y-1 mb-4">
                <div class="flex justify-between"><span class="text-gray-500">Attribute:</span> <span class="font-semibold text-gray-900">{{ $attr->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Group Section:</span> <span class="font-medium text-gray-800">{{ $attr->attributeGroup?->name ?? 'General / Other Specifications' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Input Type:</span> <span class="font-semibold text-blue-700 uppercase">{{ str_replace('_', ' ', $attr->input_type) }}</span></div>
                @if($attr->unit)
                    <div class="flex justify-between"><span class="text-gray-500">Unit:</span> <span class="font-medium text-gray-800">{{ $attr->unit->name }} ({{ $attr->unit->symbol }})</span></div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.catalog.categories.attributes.update', [$category, $attr]) }}" class="space-y-4">
                @csrf @method('PUT')

                <div class="border-t border-gray-100 pt-3 space-y-3">
                    <p class="text-xs font-bold text-gray-700">Category Specific Behavior</p>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="is_required" value="1" @checked($attr->pivot->is_required) style="accent-color:var(--theme-primary)">
                            <span class="font-semibold">Required</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="is_filterable" value="1" @checked($attr->pivot->is_filterable) style="accent-color:var(--theme-primary)">
                            <span class="font-semibold">Filterable</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="is_variant" value="1" @checked($attr->pivot->is_variant) style="accent-color:var(--theme-primary)">
                            <span class="font-semibold">Variant</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order within Category</label>
                        <input type="number" name="sort_order" value="{{ $attr->pivot->sort_order }}" placeholder="0" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

    {{-- ========================================================================= --}}
    {{-- MODAL 1: ADD ATTRIBUTE — "Choose Existing" (reuse from catalog dictionary) --}}
    {{-- or "Create New" (define a brand-new attribute, auto-assigned here)        --}}
    {{-- ========================================================================= --}}
    <div x-data="{
        open: false,
        activeTab: 'existing',
        searchQuery: '',
        selectedGroup: '',
        selectedAttrId: '',
        attributesList: {{ Js::from($availableAttributes->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'group_id' => $a->attribute_group_id,
            'group_name' => $a->attributeGroup?->name ?? 'Unassigned',
            'input_type' => $a->input_type,
            'unit' => $a->unit ? ($a->unit->name . ' (' . $a->unit->symbol . ')') : null,
            'is_required' => (bool) $a->is_required,
            'is_filterable' => (bool) $a->is_filterable,
            'is_variant' => (bool) $a->is_variant,
            'sort_order' => (int) $a->sort_order,
        ])) }},
        isRequired: false,
        isFilterable: false,
        isVariant: false,
        sortOrder: 0,
        get filteredAttributes() {
            let list = this.attributesList;
            if (this.selectedGroup !== '') {
                list = list.filter(a => a.group_id == this.selectedGroup);
            }
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.trim().toLowerCase();
                list = list.filter(a => a.name.toLowerCase().includes(q) || a.group_name.toLowerCase().includes(q));
            }
            return list;
        },
        get currentAttr() {
            return this.attributesList.find(a => a.id == this.selectedAttrId) || null;
        },
        onAttrChange() {
            if (this.currentAttr) {
                this.isRequired = this.currentAttr.is_required;
                this.isFilterable = this.currentAttr.is_filterable;
                this.isVariant = this.currentAttr.is_variant;
                this.sortOrder = this.currentAttr.sort_order;
            }
        }
    }"
    @open-add-modal.window="open = true; activeTab = 'existing'"
    x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-gray-100 overflow-hidden flex flex-col max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100 shrink-0">
                <h3 class="text-base font-bold text-gray-900">Assign Attribute to {{ $category->name }}</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="px-6 pt-3 border-b border-gray-100 shrink-0">
                <nav class="flex gap-5 -mb-px">
                    <button type="button" @click="activeTab = 'existing'"
                            class="py-2 text-sm font-semibold border-b-2 transition"
                            :class="activeTab === 'existing' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        Choose Existing
                    </button>
                    <button type="button" @click="activeTab = 'create'"
                            class="py-2 text-sm font-semibold border-b-2 transition"
                            :class="activeTab === 'create' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        Create New Attribute
                    </button>
                </nav>
            </div>

            <div class="px-6 py-4 overflow-y-auto">

                {{-- TAB: CHOOSE EXISTING --}}
                <div x-show="activeTab === 'existing'">
                    <form method="POST" action="{{ route('admin.catalog.categories.attributes.store', $category) }}" class="space-y-4">
                        @csrf

                        {{-- Search + Filter --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Search Attributes</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" x-model="searchQuery" placeholder="Search by name or group..."
                                           class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Filter by Group</label>
                                <select x-model="selectedGroup" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    <option value="">— All Groups —</option>
                                    @foreach($attributeGroups as $grp)
                                        <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Attribute Selection --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Select Attribute <span class="text-red-500">*</span></label>
                            <select name="attribute_id" x-model="selectedAttrId" @change="onAttrChange()" required
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500">
                                <option value="">— Choose an attribute —</option>
                                <template x-for="attr in filteredAttributes" :key="attr.id">
                                    <option :value="attr.id" x-text="attr.name + ' (' + attr.group_name + ')'"></option>
                                </template>
                            </select>
                            <template x-if="filteredAttributes.length === 0">
                                <p class="text-xs text-amber-600 mt-1">No matching attributes. Try clearing the search, or switch to "Create New Attribute" above.</p>
                            </template>
                        </div>

                        {{-- Attribute Summary Info --}}
                        <template x-if="currentAttr">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs space-y-1">
                                <div class="flex justify-between"><span class="text-gray-500">Group Section:</span> <span class="font-medium text-gray-800" x-text="currentAttr.group_name"></span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Input Type:</span> <span class="font-semibold text-blue-700 uppercase" x-text="currentAttr.input_type"></span></div>
                                <div class="flex justify-between" x-show="currentAttr.unit"><span class="text-gray-500">Unit:</span> <span class="font-medium text-gray-800" x-text="currentAttr.unit"></span></div>
                            </div>
                        </template>

                        {{-- Category-specific Configuration --}}
                        <div class="border-t border-gray-100 pt-3 space-y-3">
                            <p class="text-xs font-bold text-gray-700">Category Specific Behavior</p>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="is_required" value="1" x-model="isRequired" style="accent-color:var(--theme-primary)">
                                    <span class="font-semibold">Required</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="is_filterable" value="1" x-model="isFilterable" style="accent-color:var(--theme-primary)">
                                    <span class="font-semibold">Filterable</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg text-xs cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="is_variant" value="1" x-model="isVariant" style="accent-color:var(--theme-primary)">
                                    <span class="font-semibold">Variant</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order within Category</label>
                                <input type="number" name="sort_order" x-model="sortOrder" placeholder="0" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="!selectedAttrId" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50">
                                Assign Attribute
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB: CREATE NEW ATTRIBUTE — adds to the global catalog dictionary AND assigns it here --}}
                <div x-show="activeTab === 'create'">
                    <p class="text-xs text-gray-500 -mt-1 mb-4">
                        This creates a reusable attribute in the catalog dictionary (available to every category), and immediately assigns it to <strong>{{ $category->name }}</strong>.
                    </p>

                    <form method="POST" action="{{ route('admin.catalog.attributes.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.categories.attributes.index', $category) }}">
                        <input type="hidden" name="assign_to_category_id" value="{{ $category->id }}">

                        @include('backend.admin.catalog.attributes._form', [
                            'attribute' => new \App\Models\Attribute(),
                            'attributeGroups' => $attributeGroups,
                            'units' => $units,
                        ])

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                                Create &amp; Assign to Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: BULK ASSIGN BY GROUP --}}
    {{-- ========================================================================= --}}
    <div x-data="{
        open: false,
        groups: {{ Js::from($attributeGroups->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'attributes' => $g->attributes->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'input_type' => $a->input_type,
                'unit' => $a->unit?->symbol,
                'is_assigned' => in_array($a->id, $assignedIds),
            ])->toArray()
        ])) }},
        selectedGroupId: '',
        selectedAttrIds: [],
        get currentGroup() {
            return this.groups.find(g => g.id == this.selectedGroupId) || null;
        },
        selectAll() {
            if (this.currentGroup) {
                this.selectedAttrIds = this.currentGroup.attributes
                    .filter(a => !a.is_assigned)
                    .map(a => a.id);
            }
        },
        deselectAll() {
            this.selectedAttrIds = [];
        }
    }"
    @open-bulk-modal.window="open = true"
    x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-hidden"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Bulk Assign Attributes by Group</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Select an attribute group to assign multiple attributes at once.</p>
                </div>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.catalog.categories.attributes.bulk-store', $category) }}" class="space-y-4 pt-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Choose Attribute Group <span class="text-red-500">*</span></label>
                    <select x-model="selectedGroupId" @change="deselectAll()" required
                            class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500">
                        <option value="">— Select a specification section —</option>
                        <template x-for="g in groups" :key="g.id">
                            <option :value="g.id" x-text="g.name + ' (' + g.attributes.length + ' attributes)'"></option>
                        </template>
                    </select>
                </div>

                {{-- Checklist of Attributes in Selected Group --}}
                <template x-if="currentGroup">
                    <div class="space-y-2 border border-gray-200 rounded-xl p-3 bg-gray-50/60 max-h-60 overflow-y-auto">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 text-xs">
                            <span class="font-bold text-gray-800" x-text="currentGroup.name + ' Attributes'"></span>
                            <div class="flex gap-2">
                                <button type="button" @click="selectAll()" class="text-indigo-600 hover:underline text-[11px] font-semibold">Select All</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="deselectAll()" class="text-gray-500 hover:underline text-[11px]">Deselect All</button>
                            </div>
                        </div>

                        <template x-for="attr in currentGroup.attributes" :key="attr.id">
                            <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-100 text-xs hover:bg-indigo-50/40 cursor-pointer"
                                   :class="{ 'opacity-50 cursor-not-allowed bg-gray-100': attr.is_assigned }">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="attribute_ids[]" :value="attr.id" x-model="selectedAttrIds"
                                           :disabled="attr.is_assigned" style="accent-color:var(--theme-primary)">
                                    <span class="font-semibold text-gray-900" x-text="attr.name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" x-text="attr.input_type"></span>
                                    <span x-show="attr.unit" class="text-gray-500 text-[10px]" x-text="'(' + attr.unit + ')'"></span>
                                    <template x-if="attr.is_assigned">
                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Already Added</span>
                                    </template>
                                </div>
                            </label>
                        </template>

                        <template x-if="currentGroup.attributes.length === 0">
                            <p class="text-xs text-gray-400 text-center py-3">No attributes created under this group yet.</p>
                        </template>
                    </div>
                </template>

                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500" x-text="selectedAttrIds.length + ' attribute(s) selected'"></span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" :disabled="selectedAttrIds.length === 0" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50">
                            Add Selected Attributes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 3: CREATE NEW ATTRIBUTE GROUP --}}
    {{-- ========================================================================= --}}
    <div x-data="{ open: false }"
         @open-group-modal.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-100 overflow-hidden"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">New Attribute Group</h3>
                    <p class="text-xs text-gray-500 mt-0.5">A section heading used to organize related attributes (e.g. "Technical Specification").</p>
                </div>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.catalog.attribute-groups.store') }}" class="space-y-4 pt-4">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.categories.attributes.index', $category) }}">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Technical Specification"
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Description (optional)</label>
                    <textarea name="description" rows="2" placeholder="Shown as a subtitle under the group heading"
                              class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
