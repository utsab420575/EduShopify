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
                            <span class="text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full" x-text="node.attributes_count + (node.attributes_count === 1 ? ' attribute' : ' attributes')"></span>
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
        <div class="lg:col-span-4">
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

@endsection
