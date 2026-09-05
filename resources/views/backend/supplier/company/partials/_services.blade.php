{{-- ── 9. Services ── --}}
<div id="sp-section-services" class="bg-white rounded-2xl border border-gray-200 shadow-sm"
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

        @foreach($services as $service)
            <div class="mb-4 border border-gray-200 rounded-xl" :class="editing ? 'overflow-visible' : 'overflow-hidden'" x-data="{ editing: false }">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center gap-3 min-w-0">
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
                                @if($service->sort_order > 0)
                                    <span class="text-[10px] bg-gray-200/80 text-gray-600 px-1.5 py-0.5 rounded font-mono font-medium">Order: {{ $service->sort_order }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                        <button type="button" @click="editing = !editing" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition cursor-pointer" title="Edit Service">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('supplier.company.profile.services.destroy', $service) }}" onsubmit="return confirmSwal(this, 'Delete this service?', '{{ addslashes($service->title) }} will be permanently removed.', 'warning', 'Yes, delete')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer" title="Delete Service">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('supplier.company.profile.services.update', $service) }}">
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
                                <input name="sort_order" value="{{ $service->sort_order }}" type="number" min="0" max="9999" placeholder="0" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
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

        <div class="mt-2" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-violet-600 hover:text-violet-800 transition mb-4 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Service
            </button>

            <div x-show="open" x-transition x-cloak class="border border-violet-200 bg-violet-50/40 rounded-xl p-5">
                <form method="POST" action="{{ route('supplier.company.profile.services.store') }}">
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
