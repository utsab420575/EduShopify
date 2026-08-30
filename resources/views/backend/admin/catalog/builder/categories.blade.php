@extends('backend.layouts.admin')

@section('title', 'Category Builder — Categories')
@section('breadcrumb', 'Catalog & Taxonomy / Category Builder / Categories')

@section('body')

    @php($active = 'categories')
    @include('backend.admin.catalog.builder._tabs')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: {{ Js::from(collect($tree)->map(fn($n) => [
            'id' => $n['id'],
            'name' => $n['name'],
            'depth' => $n['depth'],
            'is_active' => $n['is_active'],
            'can_delete' => $n['can_delete'],
            'delete_reason' => $n['can_delete'] ? null : ($n['children_count'] > 0
                ? $n['children_count'].' subcategor'.($n['children_count'] === 1 ? 'y' : 'ies').' must be removed or reassigned first'
                : 'in use by listings'),
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

        {{-- LEFT: Category Tree --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900">All Categories</h2>
                <span class="text-xs text-gray-400">{{ count($tree) }} total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search categories..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active }"
                         :style="'padding-left:' + (12 + node.depth * 20) + 'px'">

                        <button type="button" @click="$dispatch('open-edit-category-' + node.id)"
                                class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid text-[10px]" :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400 text-sm'"></i>
                            <span class="truncate font-medium text-gray-800" x-text="node.name"></span>
                        </button>

                        <span class="flex items-center gap-1.5 shrink-0">
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-edit-category-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>

                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-category-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled :title="'Cannot delete — ' + node.delete_reason"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No categories created yet. Use "Add Category" to create the first one.</span>
                    <span x-show="allNodes.length > 0">No categories match "<span x-text="search"></span>".</span>
                </p>
            </div>

            @include('backend.admin.catalog.builder._pagination')
        </div>

        {{-- RIGHT: Add New --}}
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-folder-plus text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add a Category</h3>
                <p class="text-xs text-gray-500 mb-4">Create a top-level category, or pick a parent to nest it as a subcategory.</p>
                <button type="button" @click="$dispatch('open-create-category')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Category
                </button>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div x-data="{ open: false }" @open-create-category.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Category</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.catalog.categories.store') }}" class="space-y-4 pt-4"
                  x-data="{ dupBlocked: false }" @category-name-check="dupBlocked = $event.detail"
                  @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.categories') }}' + window.location.search">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.categories') }}">
                @include('backend.admin.catalog.categories._form', [
                    'category' => new \App\Models\Category(),
                    'parents' => $parents,
                    'existingCategories' => $tree,
                ])
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="dupBlocked" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- HIDDEN DELETE FORMS — one per deletable category, triggered by the paginated row's trash icon --}}
    @foreach($tree as $node)
        @if($node['can_delete'])
            <form x-data @delete-category-{{ $node['id'] }}.window="$el.requestSubmit()"
                  method="POST" action="{{ route('admin.catalog.categories.destroy', $node['id']) }}"
                  onsubmit="return confirmSwal(this, 'Delete Category?', 'Are you sure you want to delete &quot;{{ addslashes($node['name']) }}&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach

    {{-- EDIT MODALS — one per category --}}
    @foreach($tree as $node)
        <div x-data="{ open: false }" @open-edit-category-{{ $node['id'] }}.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "{{ $node['name'] }}"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="{{ route('admin.catalog.categories.update', $node['id']) }}" class="space-y-4 pt-4"
                      x-data="{ dupBlocked: false }" @category-name-check="dupBlocked = $event.detail"
                      @submit="$el.redirect_to.value = '{{ route('admin.catalog.builder.categories') }}' + window.location.search">
                    @csrf @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ route('admin.catalog.builder.categories') }}">
                    @include('backend.admin.catalog.categories._form', [
                        'category' => $categoryModels->get($node['id']),
                        'parents'  => $parents->where('id', '!=', $node['id'])->values(),
                        'existingCategories' => collect($tree)->reject(fn($n) => $n['id'] === $node['id'])->values(),
                    ])
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.catalog.categories.attributes.index', $node['id']) }}" class="text-xs font-medium text-indigo-600 hover:underline">
                            Manage attributes for this category &rarr;
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="dupBlocked" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection
