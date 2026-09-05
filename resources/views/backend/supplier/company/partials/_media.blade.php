{{-- ── 3. Media & Branding ── --}}
<div id="sp-section-media" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('media', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-media', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                <i class="fa-solid fa-image text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Media & Branding</p>
                <p class="text-xs text-gray-400">Logo, banner, representative photo</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="{{ route('supplier.company.profile.media.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('backend.supplier.company.partials._section-errors', ['section' => 'media'])

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Logo <span class="text-gray-400 font-normal">(square, max 2MB)</span></label>
                    <div class="flex items-center gap-4">
                        <img id="logo-preview" src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=e0e7ff&color=4f46e5' }}"
                             class="w-16 h-16 rounded-xl object-contain border border-gray-200 bg-gray-50" alt="Logo">
                        <div class="flex-1">
                            <input name="logo" type="file" accept="image/*" onchange="if(this.files[0]) document.getElementById('logo-preview').src = URL.createObjectURL(this.files[0])"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Representative Photo <span class="text-gray-400 font-normal">(max 2MB)</span></label>
                    <div class="flex items-center gap-4">
                        @if($profile?->profile_photo)
                            <img id="photo-preview" src="{{ asset('storage/'.$profile->profile_photo) }}" class="w-16 h-16 rounded-full object-cover border border-gray-200" alt="Photo">
                        @else
                            <img id="photo-preview" src="" class="w-16 h-16 rounded-full object-cover border border-gray-200" style="display:none" alt="Photo">
                            <div id="photo-placeholder" class="w-16 h-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-user text-xl"></i>
                            </div>
                        @endif
                        <div class="flex-1">
                            <input name="profile_photo" type="file" accept="image/*"
                                   onchange="if(this.files[0]){var p=document.getElementById('photo-preview');p.src=URL.createObjectURL(this.files[0]);p.style.display='block';var ph=document.getElementById('photo-placeholder');if(ph)ph.style.display='none';}"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                            @error('profile_photo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Banner Image <span class="text-gray-400 font-normal">(1200x400 recommended, max 4MB)</span></label>
                <img id="banner-preview" src="{{ $profile?->banner ? asset('storage/'.$profile->banner) : '' }}" class="w-full h-24 rounded-lg object-cover bg-gray-100 mb-3" style="{{ $profile?->banner ? '' : 'display:none' }}" alt="Banner">
                <input name="banner" type="file" accept="image/*" onchange="if(this.files[0]){var b=document.getElementById('banner-preview');b.src=URL.createObjectURL(this.files[0]);b.style.display='block';}"
                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                @error('banner') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Media
                </button>
            </div>
        </form>
    </div>
</div>
