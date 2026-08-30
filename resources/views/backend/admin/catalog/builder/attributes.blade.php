@extends('backend.layouts.admin')

@section('title', 'Category Builder — Attributes')
@section('breadcrumb', 'Catalog & Taxonomy / Category Builder / Attributes')

@section('body')

    @php($active = 'attributes')
    @include('backend.admin.catalog.builder._tabs')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: {{ Js::from($attributes->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'group_name' => $a->attributeGroup?->name ?? 'Unassigned',
            'input_type' => str_replace('_', ' ', $a->input_type),
            'is_active' => (bool) $a->is_active,
            'can_delete' => $a->listing_attribute_values_count === 0,
        ])->values()) }},
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return q === '' ? this.allNodes : this.allNodes.filter(n => (n.name + ' ' + n.group_name).toLowerCase().includes(q));
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
        get pageItems() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },
        get rangeStart() { return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
        get rangeEnd() { return Math.min(this.page * this.perPage, this.filtered.length); },
        goToPage(p) {
            this.page = Math.min(Math.max(1, p), this.totalPages);
            const url = new URL(window.location.href);
            if (this.page > 1) { url.searchParams.set('page', this.page); } else { url.searchParams.delete('page'); }
            window.history.replaceState(null, '', url);
        },
    }" x-init="goToPage(page)">

        {{-- LEFT: Attribute List --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900">All Attributes</h2>
                <span class="text-xs text-gray-400">{{ $attributes->count() }} total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search attributes or groups..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active }">
                        <button type="button" @click="$dispatch('open-modal-view-attribute-' + node.id)" class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid fa-sliders text-indigo-400"></i>
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-800" x-text="node.name"></span>
                                <span class="block text-[11px] text-gray-400" x-text="node.group_name"></span>
                            </span>
                        </button>
                        <span class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" x-text="node.input_type"></span>
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-modal-view-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="View">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled
                                    title="Cannot delete — this attribute is in use by listings"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No attributes created yet. Use "Add Attribute" to create the first one.</span>
                    <span x-show="allNodes.length > 0">No attributes match "<span x-text="search"></span>".</span>
                </p>
            </div>

            @include('backend.admin.catalog.builder._pagination')
        </div>

        {{-- RIGHT: Add New --}}
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-sliders text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add an Attribute</h3>
                <p class="text-xs text-gray-500 mb-4">A reusable specification field (e.g. Voltage, Color) available to every category.</p>
                <button type="button" @click="$dispatch('open-create-attribute')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Attribute
                </button>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div x-data="{ open: false }" @open-create-attribute.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Attribute</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.catalog.attributes.store') }}" class="space-y-4 pt-4"
                  @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.attributes') }}' + window.location.search">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.attributes') }}">
                @include('backend.admin.catalog.attributes._form', [
                    'attribute' => new \App\Models\Attribute(),
                    'attributeGroups' => $attributeGroups,
                    'units' => $units,
                    'inputTypes' => $inputTypes,
                ])
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Create Attribute</button>
                </div>
            </form>
        </div>
    </div>

    {{-- HIDDEN DELETE FORMS — one per deletable attribute, triggered by the row's trash icon --}}
    @foreach($attributes as $attr)
        @if($attr->listing_attribute_values_count === 0)
            <form x-data @delete-attribute-{{ $attr->id }}.window="$el.requestSubmit()"
                  method="POST" action="{{ route('admin.catalog.attributes.destroy', $attr) }}"
                  onsubmit="return confirmSwal(this, 'Delete Attribute?', 'Are you sure you want to delete &quot;{{ addslashes($attr->name) }}&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach

    {{-- VIEW (PREVIEW) MODALS — one per attribute --}}
    @foreach($attributes as $attr)
        <x-backend.modal :id="'view-attribute-'.$attr->id" :title="$attr->name" width="max-w-xl">
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2 flex-wrap">
                    @if($attr->is_active)
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Inactive
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 font-semibold uppercase text-[10px]">
                        {{ str_replace('_', ' ', $attr->input_type) }}
                    </span>
                    <span class="text-xs font-mono text-gray-400">{{ $attr->slug }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Group</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $attr->attributeGroup?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Unit</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $attr->unit ? "{$attr->unit->name} ({$attr->unit->symbol})" : '—' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <span class="text-xs {{ $attr->is_required ? 'text-red-700 font-semibold' : 'text-gray-400' }}">
                        <i class="fa-solid {{ $attr->is_required ? 'fa-check' : 'fa-xmark' }} mr-1"></i>Required
                    </span>
                    <span class="text-xs {{ $attr->is_filterable ? 'text-emerald-700 font-semibold' : 'text-gray-400' }}">
                        <i class="fa-solid {{ $attr->is_filterable ? 'fa-check' : 'fa-xmark' }} mr-1"></i>Filterable
                    </span>
                    <span class="text-xs {{ $attr->is_variant ? 'text-purple-700 font-semibold' : 'text-gray-400' }}">
                        <i class="fa-solid {{ $attr->is_variant ? 'fa-check' : 'fa-xmark' }} mr-1"></i>Variant
                    </span>
                </div>

                @if(in_array($attr->input_type, ['select', 'multi_select', 'color']))
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase mb-2">Predefined Values ({{ $attr->values->count() }})</p>
                        @if($attr->values->isEmpty())
                            <p class="text-xs text-gray-400">No values defined yet.</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($attr->values as $val)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium pl-2.5 pr-2.5 py-1 rounded-full"
                                          style="background:var(--theme-primary-soft); color:var(--theme-primary)">
                                        @if($attr->input_type === 'color' && $val->color_hex)
                                            <span class="w-2.5 h-2.5 rounded-full border border-white" style="background: {{ $val->color_hex }}"></span>
                                        @endif
                                        {{ $val->value }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                <a href="{{ route('admin.catalog.attributes.edit', $attr) }}" target="_self" class="text-xs font-medium text-indigo-600 hover:underline">
                    See in page &rarr;
                </a>
                <button type="button" @click="open = false; $dispatch('open-edit-attribute-{{ $attr->id }}')" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                    Edit
                </button>
            </div>
        </x-backend.modal>
    @endforeach

    {{-- EDIT MODALS — one per attribute --}}
    @foreach($attributes as $attr)
        <div x-data="{ open: false }" @open-edit-attribute-{{ $attr->id }}.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "{{ $attr->name }}"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="{{ route('admin.catalog.attributes.update', $attr) }}" class="space-y-4 pt-4"
                      @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.attributes') }}' + window.location.search">
                    @csrf @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.attributes') }}">
                    @include('backend.admin.catalog.attributes._form', [
                        'attribute' => $attr,
                        'attributeGroups' => $attributeGroups,
                        'units' => $units,
                        'inputTypes' => $inputTypes,
                    ])
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection
