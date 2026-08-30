@extends('backend.layouts.admin')

@section('title', 'RFQ Visibility Types')
@section('breadcrumb', 'Catalog & Taxonomy / Visibility Types')

@section('body')

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    form: { id: null, name: '', code: '', engine_type: 'open', max_suppliers: null, description: '', sort_order: 0, is_active: true },
    openCreate() {
        this.isEdit = false;
        this.formAction = '{{ route('admin.catalog.visibility-types.store') }}';
        this.form = { id: null, name: '', code: '', engine_type: 'open', max_suppliers: null, description: '', sort_order: 0, is_active: true };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '{{ url('admin/catalog/visibility-types') }}/' + item.id;
        this.form = {
            id: item.id,
            name: item.name,
            code: item.code,
            engine_type: item.engine_type || 'open',
            max_suppliers: item.max_suppliers,
            description: item.description || '',
            sort_order: item.sort_order,
            is_active: Boolean(item.is_active)
        };
        this.modalOpen = true;
    }
}">

    <x-backend.page-header title="RFQ Visibility Types" subtitle="Manage supplier targeting and marketplace visibility modes for buyer RFQs (Direct, Invited, Open Matching, Broadcast All).">
        <x-slot:actions>
            @can('platform.attributes.manage')
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Visibility Type</span>
            </button>
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Toolbar: Search + Status Filter + Engine Type Filter --}}
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.catalog.visibility-types.index') }}" class="flex flex-wrap items-center gap-3">
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
            <select name="engine_type" onchange="this.form.submit()"
                    class="text-xs rounded-xl border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                <option value="">All Engine Modes</option>
                <option value="open"    {{ $engineType === 'open'    ? 'selected' : '' }}>Open (Marketplace / Matchmaking)</option>
                <option value="invited" {{ $engineType === 'invited' ? 'selected' : '' }}>Invited (Restricted / Direct)</option>
            </select>
            @if($search || $status || $engineType)
                <a href="{{ route('admin.catalog.visibility-types.index') }}" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            @endif
            <input type="hidden" name="sort" value="{{ $sortField }}">
            <input type="hidden" name="direction" value="{{ $sortDir }}">
        </form>
    </div>

    @php
        $sortUrl = fn($col) => route('admin.catalog.visibility-types.index', array_merge(request()->except(['sort','direction','page']), [
            'sort'      => $col,
            'direction' => ($sortField === $col && $sortDir === 'asc') ? 'desc' : 'asc',
        ]));
        $sortIcon = fn($col) => $sortField === $col
            ? ($sortDir === 'asc' ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1"></i>' : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1"></i>')
            : '<i class="fa-solid fa-sort text-gray-400 ml-1"></i>';
    @endphp

    <x-backend.table>
        @if($visibilityTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-eye" title="No visibility types found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Name {!! $sortIcon('name') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('code') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Code {!! $sortIcon('code') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('engine_type') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Engine Mode {!! $sortIcon('engine_type') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Max Suppliers</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Meaning / Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="{{ $sortUrl('sort_order') }}" class="inline-flex items-center hover:text-gray-900 transition-colors">Order {!! $sortIcon('sort_order') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQs</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($visibilityTypes as $item)
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ $item->name }}</td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center font-mono text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $item->code }}</span>
                </td>
                <td class="px-5 py-3.5">
                    @if($item->engine_type === 'invited')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="fa-solid fa-lock text-[10px]"></i> Invited (Restricted)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-globe text-[10px]"></i> Open (Marketplace)
                        </span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-700 font-medium">
                    {{ $item->max_suppliers ? $item->max_suppliers . ' supplier(s)' : 'Unlimited' }}
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate">{{ $item->description ?: '—' }}</td>
                <td class="px-5 py-3.5 text-xs text-gray-600 font-mono">{{ $item->sort_order }}</td>
                <td class="px-5 py-3.5 text-xs text-gray-700 font-semibold">
                    <span class="px-2 py-0.5 rounded-full {{ $item->rfqs_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-gray-100 text-gray-500' }}">
                        {{ $item->rfqs_count }}
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    @can('platform.attributes.manage')
                    <form method="POST" action="{{ route('admin.catalog.visibility-types.toggle-active', $item) }}">
                        @csrf
                        <button type="submit" class="cursor-pointer">
                            <x-backend.status-badge :status="$item->is_active ? 'active' : 'inactive'" />
                        </button>
                    </form>
                    @else
                    <x-backend.status-badge :status="$item->is_active ? 'active' : 'inactive'" />
                    @endcan
                </td>
                <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                    @can('platform.attributes.manage')
                    <button type="button" @click="openEdit({{ $item->toJson() }})"
                            class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold">
                        Edit
                    </button>
                    @if($item->rfqs_count === 0)
                    <button type="button"
                            onclick="confirmDelete('delete-form-{{ $item->id }}', '{{ addslashes($item->name) }}')"
                            class="text-red-600 hover:text-red-900 text-xs font-semibold ml-2">
                        Delete
                    </button>
                    <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('admin.catalog.visibility-types.destroy', $item) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                    @endcan
                </td>
            </tr>
            @endforeach

            <x-slot:pagination>
                {{ $visibilityTypes->links() }}
            </x-slot:pagination>
        @endif
    </x-backend.table>

    {{-- Create / Edit Modal --}}
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="modalOpen = false"></div>

            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900" id="modal-title"
                                x-text="isEdit ? 'Edit Visibility Type' : 'Create Visibility Type'"></h3>
                            <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="space-y-4 text-xs">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required
                                       placeholder="e.g. Direct RFQ, Invited RFQ, Open RFQ"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Code / Identifier <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="form.code" required
                                       placeholder="e.g. direct, invited, open_matching"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent font-mono">
                                <p class="text-[11px] text-gray-400 mt-1">Unique programmatic code (a-z, 0-9, underscores, hyphens).</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Engine Mode <span class="text-red-500">*</span></label>
                                    <select name="engine_type" x-model="form.engine_type" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus-accent">
                                        <option value="open">Open (Public Marketplace)</option>
                                        <option value="invited">Invited (Restricted Shortlist)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Max Suppliers Limit</label>
                                    <input type="number" name="max_suppliers" x-model="form.max_suppliers" min="1" max="1000"
                                           placeholder="1 for single-source, blank for unlimited"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Meaning & Buyer Description</label>
                                <textarea name="description" x-model="form.description" rows="2"
                                          placeholder="Explain what this mode does to the buyer in the RFQ creation form..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" x-model="form.sort_order" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                                </div>

                                <div class="flex items-center pt-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                               class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
                                        <span class="font-semibold text-gray-700">Active / Enabled</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl border-t border-gray-100">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="btn-primary px-5 py-2 text-xs font-semibold rounded-lg shadow-xs transition">
                            <span x-text="isEdit ? 'Save Changes' : 'Create Visibility Type'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(formId, name) {
    Swal.fire({
        title: 'Delete Visibility Type?',
        text: 'Are you sure you want to delete "' + name + '"? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
@endpush

@endsection
