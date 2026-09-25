{{-- ── 3. Social Media & Web Links ── --}}
<div id="sp-section-social-links" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
     x-data="{
         open: {{ $spAccOpen('social-links', false) }},
         editModal: { open: false, action: '', platform_id: '', url: '', handle: '', label: '', is_public: true, platform_name: '' }
     }"
     x-init="$watch('open', v => localStorage.setItem('sp-acc-social-links', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                <i class="fa-solid fa-share-nodes text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Social Media &amp; Web Links</p>
                <p class="text-xs text-gray-400">
                    {{ $socialLinks->count() }} profile{{ $socialLinks->count() === 1 ? '' : 's' }} connected • LinkedIn, Facebook, Instagram, X, YouTube, and more
                </p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'social-links'])

        <div class="flex items-center justify-between mb-4">
            <div>
                <label class="block text-sm font-bold text-gray-800">Connected Social Profiles</label>
                <p class="text-xs text-gray-500">These official links are displayed on your public profile contact section for institutional buyers.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-violet-50 text-violet-700 border border-violet-200">
                {{ $socialLinks->count() }} of {{ $socialPlatforms->count() }} platforms
            </span>
        </div>

        {{-- Existing Links List --}}
        @if($socialLinks->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                @foreach($socialLinks as $link)
                    @php($platform = $link->platform)
                    <div class="p-3.5 border border-gray-200 rounded-xl bg-white hover:border-gray-300 transition-all flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border {{ $platform?->badge_color_class ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                <i class="{{ $link->platform_icon }} text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $link->platform_name }}</p>
                                    @if($link->is_public)
                                        <span class="inline-flex items-center gap-1 text-[9px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[8px]"></i> Public
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[9px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                            <i class="fa-solid fa-eye-slash text-[8px]"></i> Hidden
                                        </span>
                                    @endif
                                </div>

                                @if($link->handle)
                                    <p class="text-xs text-gray-600 truncate">{{ $link->handle }}</p>
                                @endif

                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-xs text-indigo-600 hover:underline truncate block mt-0.5">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] mr-1"></i>{{ $link->url }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Edit Link Button --}}
                            <button type="button"
                                    @click="editModal = {
                                        open: true,
                                        action: '{{ route('supplier.company.profile.social-links.update', $link) }}',
                                        platform_id: '{{ $link->social_platform_id }}',
                                        platform_name: '{{ addslashes($link->platform_name) }}',
                                        url: '{{ addslashes($link->url) }}',
                                        handle: '{{ addslashes($link->handle ?? '') }}',
                                        label: '{{ addslashes($link->label ?? '') }}',
                                        is_public: {{ $link->is_public ? 'true' : 'false' }}
                                    }"
                                    class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600 flex items-center justify-center transition cursor-pointer"
                                    title="Edit social link">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>

                            {{-- Delete Link Form --}}
                            <form method="POST" action="{{ route('supplier.company.profile.social-links.destroy', $link) }}"
                                  onsubmit="return confirmSwal(this, 'Remove this social link?', '', 'warning', 'Yes, remove')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition cursor-pointer"
                                        title="Delete social link">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-gray-400 bg-gray-50/60 rounded-xl border border-dashed border-gray-200 mb-6">
                <i class="fa-solid fa-share-nodes text-2xl text-gray-300 mb-1.5 block"></i>
                <p class="text-sm font-medium text-gray-600">No social media links connected yet.</p>
                <p class="text-xs text-gray-400 mt-0.5">Add your official channels below to enhance buyer trust and engagement.</p>
            </div>
        @endif

        {{-- Add Social Link Form --}}
        <div class="bg-gray-50/70 border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Add Social Profile or Web Link</p>
            <form method="POST" action="{{ route('supplier.company.profile.social-links.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Social Platform <span class="text-red-500">*</span></label>
                        <select name="social_platform_id" required
                                class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                            <option value="">Select Platform...</option>
                            @foreach($socialPlatforms as $platform)
                                <option value="{{ $platform->id }}" {{ old('social_platform_id') == $platform->id ? 'selected' : '' }}>
                                    {{ $platform->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('social_platform_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Profile / Page URL <span class="text-red-500">*</span></label>
                        <input name="url" value="{{ old('url') }}" type="url" placeholder="https://www.linkedin.com/company/your-brand" required
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                        @error('url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Handle / Username (Optional)</label>
                        <input name="handle" value="{{ old('handle') }}" type="text" placeholder="e.g. @yourbrand"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                        @error('handle') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Display Label (Optional)</label>
                        <input name="label" value="{{ old('label') }}" type="text" placeholder="e.g. Official LinkedIn Page"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-violet-300 outline-none">
                        @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_public" value="1" checked
                               class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                        <span class="text-xs text-gray-700 font-medium">Show publicly on company profile</span>
                    </label>

                    <button type="submit" class="inline-flex items-center justify-center gap-2 text-sm font-semibold px-5 py-2 rounded-lg text-white transition cursor-pointer shrink-0" style="background:var(--theme-primary)">
                        <i class="fa-solid fa-plus"></i> Add Social Link
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Edit Social Link Modal --}}
    <div x-show="editModal.open" x-cloak
         class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @keydown.escape.window="editModal.open = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl" @click.outside="editModal.open = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Social Link</h3>
                <button type="button" @click="editModal.open = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editModal.action" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Platform <span class="text-red-500">*</span></label>
                    <select name="social_platform_id" x-model="editModal.platform_id" required
                            class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                        @foreach($socialPlatforms as $platform)
                            <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">URL <span class="text-red-500">*</span></label>
                    <input type="url" name="url" x-model="editModal.url" required
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Handle / Username</label>
                        <input type="text" name="handle" x-model="editModal.handle" placeholder="@mybrand"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Label</label>
                        <input type="text" name="label" x-model="editModal.label" placeholder="e.g. Official Page"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2 cursor-pointer select-none pt-1">
                    <input type="checkbox" name="is_public" value="1" x-model="editModal.is_public"
                           class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                    <span class="text-xs text-gray-700 font-medium">Show publicly on company profile</span>
                </label>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModal.open = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white rounded-lg cursor-pointer" style="background:var(--theme-primary)">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
