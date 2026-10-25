{{-- ── 9. Services ── --}}
<div id="sp-section-services" data-profile-section="Services" data-filled="{{ ($services->isNotEmpty()) ? 1 : 0 }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm"
     :class="open ? 'overflow-visible' : 'overflow-hidden'"
     x-data="{
         open: {{ $spAccOpen('services', false) }},
         iconCatalog: {{ Js::from($availableIcons->map(fn($ic) => [
             'id' => $ic->id,
             'name' => $ic->name,
             'type' => $ic->icon_type,
             'value' => $ic->icon_value,
             'lib' => $ic->library?->name ?? 'Icon',
         ])) }}
     }"
     x-init="$watch('open', v => localStorage.setItem('sp-acc-services', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                <i class="fa-solid fa-briefcase text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Services</p>
                <p class="text-xs text-gray-400">{{ $services->count() > 0 ? $services->count().' service'.($services->count() > 1 ? 's' : '') : 'Services your business offers' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'services'])

        @if($services->count() > 1)
            <p class="text-xs text-gray-500 mb-3">
                <i class="fa-solid fa-grip-vertical mr-1 text-gray-400"></i>
                Drag services by the handle to change the order they appear on your public profile.
            </p>
        @endif

        {{-- Drag-and-drop list (SortableJS); the new order is saved instantly via AJAX. --}}
        <div x-data="serviceSorter(@js(supplier_route('company.profile.services.reorder')))">
        @foreach($services as $service)
            <div data-service-id="{{ $service->id }}" class="mb-4 border border-gray-200 rounded-xl bg-white" :class="editing ? 'overflow-visible' : 'overflow-hidden'" x-data="{ editing: false }">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($services->count() > 1)
                            <button type="button" class="sp-service-handle -ml-1 w-6 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing shrink-0 touch-none"
                                    title="Drag to reorder" aria-label="Drag to reorder {{ $service->title }}">
                                <i class="fa-solid fa-grip-vertical"></i>
                            </button>
                        @endif
                        @if($service->icon)
                            <div class="w-8 h-8 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center shrink-0 text-sm shadow-xs">
                                @if($service->icon->icon_type === 'fontawesome')
                                    <i class="{{ $service->icon->icon_value }}"></i>
                                @elseif($service->icon->icon_type === 'svg')
                                    <div class="w-4 h-4 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">{!! $service->icon->icon_value !!}</div>
                                @else
                                    <img src="{{ $service->icon->icon_value }}" alt="{{ $service->icon->name }}" class="w-4 h-4 object-contain">
                                @endif
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center shrink-0 text-xs">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $service->title }}</p>
                                <x-backend.status-badge :status="$service->status" />
                                <span data-order-badge class="text-[10px] bg-gray-200/80 text-gray-600 px-1.5 py-0.5 rounded font-mono font-medium {{ $service->sort_order > 0 ? '' : 'hidden' }}">Order: {{ $service->sort_order }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                        <button type="button" @click="editing = !editing" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition cursor-pointer" title="Edit Service">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <form method="POST" action="{{ supplier_route('company.profile.services.destroy', $service) }}" onsubmit="return confirmSwal(this, 'Delete this service?', '{{ addslashes($service->title) }} will be permanently removed.', 'warning', 'Yes, delete')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer" title="Delete Service">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100">
                    <form method="POST" action="{{ supplier_route('company.profile.services.update', $service) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                                <input name="title" value="{{ $service->title }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select name="status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                                    <option value="draft" {{ $service->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ $service->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $service->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Order</label>
                                <input name="sort_order" value="{{ $service->sort_order }}" data-order-input type="number" min="0" max="9999" placeholder="0" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                            </div>
                        </div>

                        @include('backend.supplier.company.partials._icon-picker', ['selectedIconId' => $service->icon_id])

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">{{ $service->description }}</textarea>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="editing = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-floppy-disk"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
        </div>{{-- /drag-and-drop list --}}

        <div class="mt-2" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-violet-600 hover:text-violet-800 transition mb-4 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Service
            </button>

            <div x-show="open" x-transition x-cloak class="border border-violet-200 bg-violet-50/40 rounded-xl p-5">
                <form method="POST" action="{{ supplier_route('company.profile.services.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                            <input name="title" value="{{ old('title') }}" type="text" placeholder="e.g. Custom Packaging" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-violet-300 outline-none">
                            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Order</label>
                            <input name="sort_order" value="{{ old('sort_order', 0) }}" type="number" min="0" max="9999" placeholder="0" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-violet-300 outline-none">
                        </div>
                    </div>

                    @include('backend.supplier.company.partials._icon-picker', ['selectedIconId' => null])

                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <textarea name="description" rows="3" placeholder="Briefly describe this service" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-violet-300 outline-none">{{ old('description') }}</textarea>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                            <i class="fa-solid fa-plus"></i> Add Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        // Services drag-and-drop. On drop, sends every service id top to
        // bottom to ServiceController@reorder, which rewrites sort_order as
        // 1..n; the "Order: N" badges / edit fields are then updated in place.
        // A failed save puts the list back the way it was.
        function serviceSorter(url) {
            return {
                saving: false,

                init() {
                    if (typeof Sortable === 'undefined') return;
                    const list = this.$el;
                    if (list.querySelectorAll('[data-service-id]').length < 2) return;

                    let before = null;
                    Sortable.create(list, {
                        handle: '.sp-service-handle',
                        draggable: '[data-service-id]',
                        animation: 160,
                        ghostClass: 'opacity-40',
                        chosenClass: 'ring-2',
                        onStart: () => { before = this.ids(); },
                        onEnd: (evt) => {
                            if (evt.oldIndex === evt.newIndex) return;
                            this.save(before);
                        },
                    });
                },

                ids() {
                    return [...this.$el.querySelectorAll(':scope > [data-service-id]')]
                        .map((el) => Number(el.dataset.serviceId));
                },

                async save(previousIds) {
                    this.saving = true;
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ ids: this.ids() }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'Could not save the new order.');

                        this.applyOrder(data.order || {});
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message || 'Service order saved.', showConfirmButton: false, timer: 1800 });
                    } catch (error) {
                        this.restore(previousIds);
                        Swal.fire({ icon: 'error', title: 'Order not saved', text: error.message, confirmButtonColor: '#4f46e5' });
                    } finally {
                        this.saving = false;
                    }
                },

                applyOrder(order) {
                    this.$el.querySelectorAll(':scope > [data-service-id]').forEach((el) => {
                        const position = order[el.dataset.serviceId];
                        if (!position) return;
                        const badge = el.querySelector('[data-order-badge]');
                        if (badge) {
                            badge.textContent = 'Order: ' + position;
                            badge.classList.remove('hidden');
                        }
                        const input = el.querySelector('[data-order-input]');
                        if (input) input.value = position;
                    });
                },

                restore(previousIds) {
                    if (!previousIds) return;
                    const list = this.$el;
                    previousIds.forEach((id) => {
                        const el = list.querySelector(`:scope > [data-service-id="${id}"]`);
                        if (el) list.appendChild(el);
                    });
                },
            };
        }
    </script>
@endpush
