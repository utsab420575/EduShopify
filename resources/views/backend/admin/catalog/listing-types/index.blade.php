@extends('backend.layouts.admin')

@section('title', 'Listing Types')
@section('breadcrumb', 'Catalog & Taxonomy / Listing Types')

@section('body')

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    form: { id: null, name: '', code: '', description: '', sort_order: 0, is_active: true },
    openCreate() {
        this.isEdit = false;
        this.formAction = '{{ route('admin.catalog.listing-types.store') }}';
        this.form = { id: null, name: '', code: '', description: '', sort_order: 0, is_active: true };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '{{ url('admin/catalog/listing-types') }}/' + item.id;
        this.form = { id: item.id, name: item.name, code: item.code, description: item.description || '', sort_order: item.sort_order, is_active: Boolean(item.is_active) };
        this.modalOpen = true;
    }
}">

    <x-backend.page-header title="Listing Types" subtitle="Define the types of listings suppliers can create (e.g. Product, Service). Drives listing-specific form flows.">
        <x-slot:actions>
            @can('platform.attributes.manage')
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Listing Type</span>
            </button>
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Toolbar --}}
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.catalog.listing-types.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, code..."
                       class="w-full text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-white focus-accent transition"
                       @input.debounce.500ms="$event.target.form.requestSubmit()"
                       x-init="if (new URLSearchParams(window.location.search).get('search')) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }">
            </div>
            <select name="status" onchange="this.form.submit()"
                    class="text-xs rounded-xl border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                <option value="">All Statuses</option>
                <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @if($search || $status)
                <a href="{{ route('admin.catalog.listing-types.index') }}" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            @endif
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
        </form>
    </div>

    @php
        $sortUrl = fn($col) => route('admin.catalog.listing-types.index', array_merge(request()->except(['sort','direction','page']), [
            'sort'      => $col,
            'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
        ]));
        $sortIcon = fn($col) => $sort === $col
            ? ($direction === 'asc' ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1"></i>' : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1"></i>')
            : '<i class="fa-solid fa-sort text-gray-400 ml-1"></i>';
    @endphp

    <x-backend.table>
        @if($listingTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-boxes-stacked" title="No listing types found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Name {!! $sortIcon('name') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('code') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Code {!! $sortIcon('code') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('sort_order') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Order {!! $sortIcon('sort_order') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Listings</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($listingTypes as $item)
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs"
                              style="background:var(--theme-primary-soft);color:var(--theme-primary)">
                            <i class="fa-solid {{ $item->code === 'product' ? 'fa-box' : 'fa-screwdriver-wrench' }}"></i>
                        </span>
                        {{ $item->name }}
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center font-mono text-xs font-bold px-2 py-0.5 rounded bg-violet-50 text-violet-700 border border-violet-100">{{ $item->code }}</span>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate">{{ $item->description ?: '—' }}</td>
                <td class="px-5 py-3.5 text-sm text-gray-600 font-mono">{{ $item->sort_order }}</td>
                <td class="px-5 py-3.5 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                        <i class="fa-solid fa-layer-group text-[10px]"></i> {{ number_format($item->listings_count) }}
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <x-backend.status-badge :status="$item->is_active ? 'active' : 'inactive'" />
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        @can('platform.attributes.manage')
                        <form method="POST" action="{{ route('admin.catalog.listing-types.toggle-active', $item) }}"
                              onsubmit="return confirmSwal(this, '{{ $item->is_active ? 'Deactivate' : 'Activate' }} Listing Type?', '{{ addslashes($item->name) }} will be {{ $item->is_active ? 'hidden from suppliers' : 'made available' }}.', 'question', 'Yes, {{ $item->is_active ? 'Deactivate' : 'Activate' }}')">
                            @csrf
                            <button type="submit"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center transition {{ $item->is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                                    title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fa-solid {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>

                        <button type="button"
                                @click="openEdit({{ json_encode(['id' => $item->id, 'name' => $item->name, 'code' => $item->code, 'description' => $item->description, 'sort_order' => $item->sort_order, 'is_active' => (bool)$item->is_active]) }})"
                                class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 transition"
                                title="Edit">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>

                        <form method="POST" action="{{ route('admin.catalog.listing-types.destroy', $item) }}"
                              onsubmit="return confirmSwal(this, 'Delete Listing Type?', 'Delete {{ addslashes($item->name) }}? This cannot be undone.', 'warning', 'Yes, Delete')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Delete">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach

            <x-slot:pagination>
                <x-backend.pagination :paginator="$listingTypes" />
            </x-slot:pagination>
        @endif
    </x-backend.table>

    {{-- Modal --}}
    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEdit ? 'Edit Listing Type' : 'New Listing Type'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">Defines the category of listing suppliers create (product, service, etc.).</p>
                        </div>
                        <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="form.name" required placeholder="e.g. Product"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" x-model="form.code" required placeholder="e.g. product" maxlength="50"
                                   :readonly="isEdit"
                                   :class="isEdit ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                   class="w-full text-sm font-mono rounded-lg border border-gray-300 px-3 py-2 focus-accent transition">
                            <p class="text-[11px] text-gray-400 mt-1">Unique slug — drives form logic. Cannot be changed after creation.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                            <textarea name="description" x-model="form.description" rows="2" placeholder="Short description..."
                                      class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" x-model.number="form.sort_order" min="0"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded" style="accent-color:var(--theme-primary)">
                                    <span class="text-xs font-semibold text-gray-800">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 rounded-b-2xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-5 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="isEdit ? 'Save Changes' : 'Create Listing Type'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection
