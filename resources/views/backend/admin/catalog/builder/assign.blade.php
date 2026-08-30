@extends('backend.layouts.admin')

@section('title', 'Category Builder — Assign to Category')
@section('breadcrumb', 'Catalog & Taxonomy / Category Builder / Assign to Category')

@section('body')

    @php($active = 'assign')
    @include('backend.admin.catalog.builder._tabs')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        // Left: category tree (search + pagination — same pattern as the Category tab)
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: {{ Js::from(collect($tree)->map(fn($n) => [
            'id' => $n['id'], 'name' => $n['name'], 'depth' => $n['depth'],
            'is_active' => $n['is_active'], 'attributes_count' => $n['attributes_count'],
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

        // Right: attribute assignment checklist for whichever category is selected.
        // selectedIds is the FULL desired state (assigned + newly checked, minus
        // unchecked) — submitted as-is to a sync endpoint that attaches/detaches
        // to match it exactly, so this one checklist handles both add and remove.
        selectedCategoryId: {{ Js::from($selectedCategoryId) }},
        selectedCategoryName: {{ Js::from($selectedCategoryId ? (collect($tree)->firstWhere('id', $selectedCategoryId)['name'] ?? null) : null) }},
        assignments: {{ Js::from($assignments) }},
        groups: {{ Js::from($groupedAttributes) }},
        attrSearch: '',
        selectedIds: {{ Js::from($selectedCategoryId ? collect($assignments[$selectedCategoryId] ?? [])->values()->all() : []) }},
        originalIds: {{ Js::from($selectedCategoryId ? collect($assignments[$selectedCategoryId] ?? [])->values()->all() : []) }},

        selectCategory(node) {
            this.selectedCategoryId = node.id;
            this.selectedCategoryName = node.name;
            const assigned = [...(this.assignments[node.id] || [])];
            this.selectedIds = assigned;
            this.originalIds = assigned;
            this.attrSearch = '';
        },
        wasAssigned(id) { return this.originalIds.includes(id); },
        isPendingAdd(id) { return this.selectedIds.includes(id) && !this.wasAssigned(id); },
        isPendingRemove(id) { return !this.selectedIds.includes(id) && this.wasAssigned(id); },
        get pendingAddCount() { return this.selectedIds.filter(id => !this.wasAssigned(id)).length; },
        get pendingRemoveCount() { return this.originalIds.filter(id => !this.selectedIds.includes(id)).length; },
        get hasChanges() { return this.pendingAddCount > 0 || this.pendingRemoveCount > 0; },
        get filteredGroups() {
            const q = this.attrSearch.trim().toLowerCase();
            if (q === '') return this.groups;
            return this.groups
                .map(g => ({ ...g, attributes: g.attributes.filter(a => a.name.toLowerCase().includes(q) || g.group_name.toLowerCase().includes(q)) }))
                .filter(g => g.attributes.length > 0);
        },
        isGroupFullySelected(group) {
            const ids = group.attributes.map(a => a.id);
            return ids.length > 0 && ids.every(id => this.selectedIds.includes(id));
        },
        toggleGroup(group) {
            const ids = group.attributes.map(a => a.id);
            if (this.isGroupFullySelected(group)) {
                this.selectedIds = this.selectedIds.filter(id => !ids.includes(id));
            } else {
                this.selectedIds = [...new Set([...this.selectedIds, ...ids])];
            }
        },
    }" x-init="goToPage(page)">

        {{-- LEFT: Category Tree --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900">Categories</h2>
                <span class="text-xs text-gray-400">{{ count($tree) }} total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search categories..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <button type="button" @click="selectCategory(node)"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left text-sm border"
                            :class="selectedCategoryId === node.id ? 'border-indigo-200' : 'border-transparent hover:bg-gray-50'"
                            :style="(selectedCategoryId === node.id ? 'background:var(--theme-primary-soft);' : '') + 'padding-left:' + (12 + node.depth * 20) + 'px'">
                        <span class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid text-[10px]" :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400 text-sm'"></i>
                            <span class="truncate font-medium" :class="selectedCategoryId === node.id ? 'text-indigo-700' : 'text-gray-800'" x-text="node.name"></span>
                        </span>
                        <span class="text-[10px] font-semibold shrink-0 px-1.5 py-0.5 rounded-full"
                              :class="node.attributes_count > 0 ? 'text-indigo-700 bg-white border border-indigo-100' : 'text-gray-400 bg-gray-50 border border-gray-200'"
                              x-text="node.attributes_count + (node.attributes_count === 1 ? ' attribute' : ' attributes')"></span>
                    </button>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No categories yet. Create one in the <a href="{{ route('admin.catalog.builder.categories') }}" class="text-indigo-600 hover:underline">Category</a> tab first.</span>
                    <span x-show="allNodes.length > 0">No categories match "<span x-text="search"></span>".</span>
                </p>
            </div>

            @include('backend.admin.catalog.builder._pagination')
        </div>

        {{-- RIGHT: Attribute Assignment Checklist --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-gray-200 p-5">

            <template x-if="!selectedCategoryId">
                <div class="flex flex-col items-center justify-center text-center py-16">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-arrow-left text-lg"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Pick a category</h3>
                    <p class="text-xs text-gray-500 max-w-xs">Choose a category on the left to see and assign its specification attributes.</p>
                </div>
            </template>

            <template x-if="selectedCategoryId">
                <form method="POST" :action="'{{ url('/admin/catalog/categories') }}/' + selectedCategoryId + '/attributes/sync'">
                    @csrf
                    <input type="hidden" name="redirect_to" :value="'{{ route('admin.catalog.builder.assign') }}?category=' + selectedCategoryId + (page > 1 ? '&page=' + page : '')">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="attribute_ids[]" :value="id">
                    </template>

                    <div class="flex items-center justify-between mb-4">
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-gray-900 truncate">Attributes for "<span x-text="selectedCategoryName"></span>"</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Check to assign, uncheck to remove — then save.</p>
                        </div>
                        <a :href="'{{ url('/admin/catalog/categories') }}/' + selectedCategoryId + '/attributes'" target="_self"
                           class="text-xs font-medium text-indigo-600 hover:underline shrink-0 ml-3">
                            Full settings &rarr;
                        </a>
                    </div>

                    <div class="relative mb-4">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="attrSearch" placeholder="Search attributes or groups..."
                               class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
                    </div>

                    <div class="border border-gray-200 rounded-lg h-[420px] lg:h-[480px] overflow-y-auto divide-y divide-gray-100">
                        <template x-for="group in filteredGroups" :key="group.group_id">
                            <div>
                                <label class="flex items-center gap-2.5 px-3 py-2 bg-gray-50/80 cursor-pointer">
                                    <input type="checkbox" :checked="isGroupFullySelected(group)" @change="toggleGroup(group)"
                                           style="accent-color:var(--theme-primary)">
                                    <span class="text-xs font-bold text-gray-800" x-text="group.group_name"></span>
                                    <span class="text-[10px] text-gray-400" x-text="'(' + group.attributes.length + ')'"></span>
                                </label>
                                <template x-for="attr in group.attributes" :key="attr.id">
                                    <label class="flex items-start gap-2.5 px-3 py-2 pl-8 hover:bg-gray-50 cursor-pointer text-sm">
                                        <input type="checkbox" :value="attr.id" x-model.number="selectedIds" style="accent-color:var(--theme-primary)" class="mt-0.5 shrink-0">
                                        <span class="min-w-0">
                                            <span class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-gray-800" x-text="attr.name"></span>
                                                <span x-show="isPendingRemove(attr.id)" class="text-[10px] font-semibold text-red-700 bg-red-50 px-1.5 py-0.5 rounded-full border border-red-200">Will be removed</span>
                                                <span x-show="isPendingAdd(attr.id)" class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded-full border border-blue-200">Will be added</span>
                                                <span x-show="wasAssigned(attr.id) && !isPendingRemove(attr.id)" class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-full border border-emerald-200">Assigned</span>
                                            </span>
                                            <span x-show="attr.values && attr.values.length > 0" class="block text-[11px] text-gray-400 mt-0.5">
                                                Options: <span x-text="attr.values.join(', ')"></span>
                                            </span>
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <p x-show="filteredGroups.length === 0" class="text-sm text-gray-400 text-center py-10">
                            No attributes match "<span x-text="attrSearch"></span>".
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 mt-1">
                        <span class="text-xs text-gray-500">
                            <template x-if="hasChanges">
                                <span>
                                    <span x-show="pendingAddCount > 0" class="text-blue-700 font-medium" x-text="pendingAddCount + ' to add'"></span>
                                    <span x-show="pendingAddCount > 0 && pendingRemoveCount > 0">, </span>
                                    <span x-show="pendingRemoveCount > 0" class="text-red-700 font-medium" x-text="pendingRemoveCount + ' to remove'"></span>
                                </span>
                            </template>
                            <template x-if="!hasChanges">
                                <span>No changes</span>
                            </template>
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectedIds = [...originalIds]" :disabled="!hasChanges"
                                    class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                                <i class="fa-solid fa-rotate-left text-xs mr-1.5"></i> Reset to Previous
                            </button>
                            <button type="submit" :disabled="!hasChanges" class="btn-primary text-sm font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-check text-xs mr-1.5"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </template>
        </div>
    </div>

@endsection
