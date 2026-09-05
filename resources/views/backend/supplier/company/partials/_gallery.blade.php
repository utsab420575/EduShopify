{{-- ── 4. Gallery & Videos ── --}}
<div id="sp-section-gallery" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('gallery', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-gallery', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600">
                <i class="fa-solid fa-images text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Gallery & Videos</p>
                <p class="text-xs text-gray-400">{{ $existingGallery->count() }} photo{{ $existingGallery->count() === 1 ? '' : 's' }}, {{ $videos->count() }} video{{ $videos->count() === 1 ? '' : 's' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'gallery'])

        <label class="block text-sm font-medium text-gray-700 mb-3">Photos</label>

        @if($existingGallery->isNotEmpty())
            <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                @foreach($existingGallery as $img)
                    <div class="relative group aspect-square">
                        <img src="{{ asset('storage/'.$img->image_path) }}" class="w-full h-full object-cover rounded-xl border border-gray-200" alt="">
                        <form method="POST" action="{{ route('supplier.company.profile.gallery.destroy', $img) }}" onsubmit="return confirmSwal(this, 'Remove this image?', '', 'warning', 'Yes, remove')" class="absolute top-1 right-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-5 h-5 rounded-full bg-red-500 text-white text-[9px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('supplier.company.profile.gallery.store') }}" enctype="multipart/form-data"
              x-data="{ previews: [] }">
            @csrf

            <template x-if="previews.length">
                <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                    <template x-for="(src, i) in previews" :key="i">
                        <div class="relative aspect-square">
                            <img :src="src" class="w-full h-full object-cover rounded-xl border-2 border-pink-300" alt="">
                            <span class="absolute bottom-1 left-1 text-[8px] font-bold text-white bg-pink-500 px-1 rounded">NEW</span>
                        </div>
                    </template>
                </div>
            </template>

            <label class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-pink-400 hover:bg-pink-50 transition-colors">
                <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-xl"></i>
                <span class="text-sm text-gray-500">Click to add photos</span>
                <input type="file" name="photos[]" accept="image/*" multiple class="hidden"
                       @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
            </label>
            <p class="mt-1 text-xs text-gray-400">JPG, PNG, GIF, WEBP or BMP • Up to 5MB each</p>
            @error('photos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            @error('photos.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

            <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Photos
                </button>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-100">
            <label class="block text-sm font-medium text-gray-700 mb-3">Videos</label>

            @if($videos->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    @foreach($videos as $video)
                        <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-play"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $video->title }}</p>
                                    <a href="{{ $video->video_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline truncate block">{{ $video->video_url }}</a>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('supplier.company.profile.videos.destroy', $video) }}" onsubmit="return confirmSwal(this, 'Remove this video?', '', 'warning', 'Yes, remove')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 shrink-0 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('supplier.company.profile.videos.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Video Title</label>
                    <input name="title" value="{{ old('title') }}" type="text" placeholder="e.g. Factory Tour" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Video URL</label>
                    <input name="video_url" value="{{ old('video_url') }}" type="url" placeholder="https://www.youtube.com/watch?v=..." class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    @error('video_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 text-sm font-medium px-4 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-plus"></i> Add Video
                </button>
            </form>
        </div>
    </div>
</div>
