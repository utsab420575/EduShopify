@extends('backend.layouts.admin')

@section('title', 'Category Builder — Attribute Groups')
@section('breadcrumb', 'Catalog & Taxonomy / Category Builder / Attribute Groups')

@section('body')

    @php($active = 'attribute-groups')
    @include('backend.admin.catalog.builder._tabs')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: {{ Js::from($groups->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'is_active' => (bool) $g->is_active,
            'attributes_count' => $g->attributes_count,
            'can_delete' => $g->attributes_count === 0,
        ])->values()) }},
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return q === '' ? this.allNodes : this.allNodes.filter(n => n.name.toLowerCase().includes(q));
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

        {{-- LEFT: Group List --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900">All Attribute Groups</h2>
                <span class="text-xs text-gray-400">{{ $groups->count() }} total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search attribute groups..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active }">
                        <button type="button" @click="$dispatch('open-modal-view-group-' + node.id)" class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid fa-layer-group text-indigo-400"></i>
                            <span class="truncate font-medium text-gray-800" x-text="node.name"></span>
                        </button>
                        <span class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="$dispatch('open-group-attributes-' + node.id)"
                                    x-show="node.attributes_count > 0"
                                    class="text-[10px] font-medium text-gray-600 bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 px-1.5 py-0.5 rounded-full transition"
                                    title="View attributes in this group"
                                    x-text="node.attributes_count + (node.attributes_count === 1 ? ' attribute' : ' attributes')"></button>
                            <span x-show="node.attributes_count === 0" class="text-[10px] font-medium text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">0 attributes</span>
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-modal-view-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="View">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled
                                    title="Cannot delete — has assigned attributes, reassign or remove them first"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No attribute groups created yet. Use "Add Group" to create the first one.</span>
                    <span x-show="allNodes.length > 0">No attribute groups match "<span x-text="search"></span>".</span>
                </p>
            </div>

            @include('backend.admin.catalog.builder._pagination')
        </div>

        {{-- RIGHT: Add New --}}
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add an Attribute Group</h3>
                <p class="text-xs text-gray-500 mb-4">A section heading used to organize related attributes (e.g. "Technical Specification").</p>
                <button type="button" @click="$dispatch('open-create-group')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Group
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-csv text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Import Attribute Groups</h3>
                <p class="text-xs text-gray-500 mb-4">Upload a CSV of group names to create several at once — no form filling needed.</p>
                <button type="button" @click="$dispatch('open-import-groups')" class="text-sm font-semibold px-4 py-2.5 rounded-lg w-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
                    <i class="fa-solid fa-upload text-xs mr-1.5"></i> Import CSV
                </button>
                <a href="{{ route('admin.catalog.attribute-groups.import.template') }}" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-download mr-1"></i> Download template
                </a>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div x-data="{ open: false }" @open-create-group.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Attribute Group</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.catalog.attribute-groups.store') }}" class="space-y-4 pt-4"
                  @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.attribute-groups') }}' + window.location.search">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.attribute-groups') }}">
                @include('backend.admin.catalog.attribute-groups._form', ['group' => new \App\Models\AttributeGroup()])
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Create Group</button>
                </div>
            </form>
        </div>
    </div>

    {{-- IMPORT MODAL — upload a CSV of attribute group names --}}
    <div x-data="{ open: {{ session('open_group_import') ? 'true' : 'false' }} }" @open-import-groups.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Attribute Groups</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="pt-4 space-y-4">
                <p class="text-xs text-gray-500">
                    Upload a CSV with <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name</code> and
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">description</code> columns — one attribute
                    group per row, both required. Everything else is set automatically: new groups start active, are
                    appended after existing ones, and are stamped with who imported them.
                    Groups that already exist (matched by name) are reused, never duplicated, so it's safe to re-upload the same file.
                </p>
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-[11px] font-mono text-gray-600 leading-5">
                    name,description<br>
                    Technical Specification,Core specifications suppliers must fill in.<br>
                    Physical Dimensions,Size and weight of the product.
                </div>

                @error('file')
                    <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror

                <form method="POST" action="{{ route('admin.catalog.attribute-groups.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CSV File</label>
                        <input type="file" name="file" accept=".csv,text/csv" required
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg">
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.catalog.attribute-groups.import.template') }}" class="text-xs font-medium text-indigo-600 hover:underline">
                            <i class="fa-solid fa-download mr-1"></i> Download template
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Preview Import</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- IMPORT PREVIEW MODAL — shown after a successful preview, confirms before writing --}}
    @if(session('group_import_preview'))
        @php($groupImportPreview = session('group_import_preview'))
        <div x-data="{ open: {{ session('open_group_import_preview') ? 'true' : 'false' }} }"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 max-h-[88vh] flex flex-col"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                    <h3 class="text-base font-bold text-gray-900">Review Import</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="py-4 flex items-center gap-4 shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-plus"></i> {{ $groupImportPreview['created_count'] }} new
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                        <i class="fa-solid fa-check"></i> {{ $groupImportPreview['existing_count'] }} already exist (will be reused)
                    </span>
                    @if($groupImportPreview['error_count'] > 0)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $groupImportPreview['error_count'] }} row error{{ $groupImportPreview['error_count'] === 1 ? '' : 's' }}
                        </span>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    @foreach($groupImportPreview['rows'] as $row)
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            @if(isset($row['error']))
                                <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Line {{ $row['line'] }}: {{ $row['name'] }} — {{ $row['error'] }}</p>
                            @else
                                <p class="text-xs flex flex-wrap items-center gap-2">
                                    <span class="{{ $row['status'] === 'existing' ? 'text-gray-500' : 'text-emerald-700 font-semibold' }}">{{ $row['name'] }}</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $row['status'] === 'would_create' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-500' }}">
                                        {{ $row['status'] === 'would_create' ? 'Creates new' : 'Already exists' }}
                                    </span>
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 mt-2 border-t border-gray-100 shrink-0">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="{{ route('admin.catalog.attribute-groups.import.store') }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- HIDDEN DELETE FORMS — one per deletable group, triggered by the row's trash icon --}}
    @foreach($groups as $group)
        @if($group->attributes_count === 0)
            <form x-data @delete-group-{{ $group->id }}.window="$el.requestSubmit()"
                  method="POST" action="{{ route('admin.catalog.attribute-groups.destroy', $group) }}"
                  onsubmit="return confirmSwal(this, 'Delete Attribute Group?', 'Are you sure you want to delete &quot;{{ addslashes($group->name) }}&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach

    {{-- VIEW (PREVIEW) MODALS — one per group --}}
    @foreach($groups as $group)
        <x-backend.modal :id="'view-group-'.$group->id" :title="$group->name">
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2">
                    @if($group->is_active)
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Inactive
                        </span>
                    @endif
                    <span class="text-xs font-mono text-gray-400">{{ $group->slug }}</span>
                </div>

                <p class="text-sm text-gray-600">{{ $group->description ?: 'No description provided.' }}</p>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Attributes</p>
                        <p class="text-lg font-bold text-gray-900">{{ $group->attributes_count }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Sort Order</p>
                        <p class="text-lg font-bold text-gray-900">{{ $group->sort_order }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                <a href="{{ route('admin.catalog.attribute-groups.edit', $group) }}" target="_self" class="text-xs font-medium text-indigo-600 hover:underline">
                    See in page &rarr;
                </a>
                <button type="button" @click="open = false; $dispatch('open-edit-group-{{ $group->id }}')" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                    Edit
                </button>
            </div>
        </x-backend.modal>
    @endforeach

    {{-- EDIT MODALS — one per group --}}
    @foreach($groups as $group)
        <div x-data="{ open: false }" @open-edit-group-{{ $group->id }}.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "{{ $group->name }}"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="{{ route('admin.catalog.attribute-groups.update', $group) }}" class="space-y-4 pt-4"
                      @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.attribute-groups') }}' + window.location.search">
                    @csrf @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.attribute-groups') }}">
                    @include('backend.admin.catalog.attribute-groups._form', ['group' => $group])
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- GROUP ATTRIBUTES MODAL — lists every attribute in the group with a
         quick active/inactive toggle and a link to its full edit page, so an
         admin doesn't have to leave this tab to see what's inside a group or
         to flip one on/off. --}}
    @foreach($groups as $group)
        <div x-data="{ open: {{ session('open_group_attributes_id') == $group->id ? 'true' : 'false' }} }"
             @open-group-attributes-{{ $group->id }}.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Attributes in "{{ $group->name }}"</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $group->attributes->count() }} attribute{{ $group->attributes->count() === 1 ? '' : 's' }} — toggle active/inactive or edit directly.</p>
                    </div>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="pt-3 space-y-1.5 max-h-[60vh] overflow-y-auto">
                    @forelse($group->attributes as $attr)
                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 {{ $attr->is_active ? '' : 'opacity-60' }}">
                            <div class="min-w-0 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-indigo-400 text-xs shrink-0"></i>
                                <span class="truncate text-sm font-medium text-gray-800">{{ $attr->name }}</span>
                                <span class="shrink-0 text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ str_replace('_', ' ', $attr->input_type) }}</span>
                                @unless($attr->is_active)
                                    <span class="shrink-0 text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>
                                @endunless
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <form method="POST" action="{{ route('admin.catalog.attributes.toggle-active', $attr) }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.attribute-groups') }}">
                                    <input type="hidden" name="reopen_group_id" value="{{ $group->id }}">
                                    <button type="submit" class="w-7 h-7 rounded-lg inline-flex items-center justify-center hover:bg-gray-100" title="{{ $attr->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fa-solid {{ $attr->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }} text-base"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.catalog.attributes.edit', $attr) }}" target="_self"
                                   class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit attribute">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-8">No attributes in this group yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

@endsection
