{{-- ── 4. Gallery & Videos ── --}}
<div id="sp-section-gallery" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
     x-data="{
         open: {{ $spAccOpen('gallery', false) }},
         lightbox: { open: false, src: '', caption: '' },
         editPhoto: { open: false, action: '', caption: '', alt: '', src: '' },
         previewVideo: { open: false, title: '', url: '', isDirect: false, embedUrl: '' },
         editVideo: { open: false, action: '', title: '', url: '', caption: '' },
         newVideoUrl: '{{ old('video_url', '') }}',
         detectProvider(url) {
             if (!url) return null;
             if (url.includes('youtube.com') || url.includes('youtu.be')) return { name: 'YouTube', color: 'text-red-600 bg-red-50 border-red-200', icon: 'fa-brands fa-youtube' };
             if (url.includes('vimeo.com')) return { name: 'Vimeo', color: 'text-sky-600 bg-sky-50 border-sky-200', icon: 'fa-brands fa-vimeo-v' };
             if (/\.(mp4|webm|ogg|ogv|mov|m4v)(\?.*)?$/i.test(url)) return { name: 'Direct MP4 / Web Video', color: 'text-emerald-600 bg-emerald-50 border-emerald-200', icon: 'fa-solid fa-file-video' };
             return { name: 'Web Video Source', color: 'text-indigo-600 bg-indigo-50 border-indigo-200', icon: 'fa-solid fa-play' };
         }
     }"
     x-init="$watch('open', v => localStorage.setItem('sp-acc-gallery', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600">
                <i class="fa-solid fa-images text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Gallery &amp; Videos</p>
                <p class="text-xs text-gray-400">{{ $existingGallery->count() }} photo{{ $existingGallery->count() === 1 ? '' : 's' }}, {{ $videos->count() }} video{{ $videos->count() === 1 ? '' : 's' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'gallery'])

        {{-- ── 1. GALLERY PHOTOS ── --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <label class="block text-sm font-bold text-gray-800">Gallery Photos</label>
                    <p class="text-xs text-gray-500">Showcase your facilities, team, showroom, and product samples on your public profile.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-pink-50 text-pink-700 border border-pink-200">
                    {{ $existingGallery->count() }} uploaded
                </span>
            </div>

            @if($existingGallery->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-3 mb-5">
                    @foreach($existingGallery as $img)
                        <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-100 shadow-sm">
                            <img src="{{ $img->image_url }}" class="w-full h-full object-cover transition duration-200 group-hover:scale-105" alt="{{ $img->alt_text ?? 'Gallery image' }}">

                            @if($img->caption)
                                <div class="absolute bottom-0 inset-x-0 bg-black/60 backdrop-blur-xs text-[10px] text-white px-1.5 py-0.5 truncate text-center">
                                    {{ $img->caption }}
                                </div>
                            @endif

                            {{-- Hover Actions Overlay --}}
                            <div class="absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 p-1">
                                {{-- View button --}}
                                <button type="button"
                                        @click="lightbox = { open: true, src: '{{ $img->image_url }}', caption: '{{ addslashes($img->caption ?? '') }}' }"
                                        class="w-7 h-7 rounded-lg bg-white/90 hover:bg-white text-gray-800 text-xs flex items-center justify-center shadow transition cursor-pointer"
                                        title="View full image">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                                {{-- Edit caption button --}}
                                <button type="button"
                                        @click="editPhoto = { open: true, action: '{{ route('supplier.company.profile.gallery.update', $img) }}', caption: '{{ addslashes($img->caption ?? '') }}', alt: '{{ addslashes($img->alt_text ?? '') }}', src: '{{ $img->image_url }}' }"
                                        class="w-7 h-7 rounded-lg bg-white/90 hover:bg-white text-indigo-600 text-xs flex items-center justify-center shadow transition cursor-pointer"
                                        title="Edit caption">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                {{-- Delete button --}}
                                <form method="POST" action="{{ route('supplier.company.profile.gallery.destroy', $img) }}"
                                      onsubmit="return confirmSwal(this, 'Remove this image?', '', 'warning', 'Yes, remove')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs flex items-center justify-center shadow transition cursor-pointer"
                                            title="Delete image">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
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

                <label class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-pink-400 hover:bg-pink-50/50 transition-colors">
                    <i class="fa-solid fa-cloud-arrow-up text-pink-500 text-xl"></i>
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-700">Click to upload photos</span>
                        <span class="text-xs text-gray-400 ml-2">Supports JPG, PNG, GIF, WEBP • Up to 5MB each</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-white border border-gray-200 text-gray-600 shadow-xs">Select Files</span>
                    <input type="file" name="photos[]" accept="image/*" multiple class="hidden"
                           @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                </label>
                @error('photos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @error('photos.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                <div class="flex justify-end mt-4">
                    <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                        <i class="fa-solid fa-floppy-disk"></i> Upload Photos
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 2. VIDEOS SECTION ── --}}
        <div class="pt-6 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <label class="block text-sm font-bold text-gray-800">Company &amp; Product Videos</label>
                    <p class="text-xs text-gray-500">Add YouTube links, Vimeo videos, or direct video file links (.mp4, .webm, etc.).</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                    {{ $videos->count() }} video{{ $videos->count() === 1 ? '' : 's' }}
                </span>
            </div>

            @if($videos->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                    @foreach($videos as $video)
                        @php($badge = $video->providerBadge())
                        <div class="p-4 border border-gray-200 rounded-xl bg-white hover:border-gray-300 transition-all flex flex-col justify-between gap-3 shadow-xs">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border {{ $badge['bg_class'] }}">
                                    <i class="{{ $badge['icon'] }} text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $badge['bg_class'] }}">
                                            <i class="{{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $video->title }}</p>
                                    @if($video->caption)
                                        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $video->caption }}</p>
                                    @endif
                                    <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-xs text-indigo-600 hover:underline truncate block mt-1">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] mr-1"></i>{{ $video->video_url }}
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                {{-- Preview Player Button --}}
                                <button type="button"
                                        @click="previewVideo = {
                                            open: true,
                                            title: '{{ addslashes($video->title) }}',
                                            url: '{{ $video->video_url }}',
                                            isDirect: {{ $video->isDirectVideo() || !in_array($video->resolvedProvider(), ['youtube', 'vimeo']) ? 'true' : 'false' }},
                                            embedUrl: '{{ $video->embedUrl() }}'
                                        }"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition cursor-pointer">
                                    <i class="fa-solid fa-play text-[10px]"></i> Watch Preview
                                </button>

                                <div class="flex items-center gap-1.5">
                                    {{-- Edit Video Button --}}
                                    <button type="button"
                                            @click="editVideo = {
                                                open: true,
                                                action: '{{ route('supplier.company.profile.videos.update', $video) }}',
                                                title: '{{ addslashes($video->title) }}',
                                                url: '{{ addslashes($video->video_url) }}',
                                                caption: '{{ addslashes($video->caption ?? '') }}'
                                            }"
                                            class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600 flex items-center justify-center transition cursor-pointer"
                                            title="Edit video">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>

                                    {{-- Delete Video Form --}}
                                    <form method="POST" action="{{ route('supplier.company.profile.videos.destroy', $video) }}"
                                          onsubmit="return confirmSwal(this, 'Remove this video?', '', 'warning', 'Yes, remove')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition cursor-pointer"
                                                title="Delete video">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Add Video Form --}}
            <div class="bg-gray-50/70 border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Add New Video</p>
                <form method="POST" action="{{ route('supplier.company.profile.videos.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Video Title <span class="text-red-500">*</span></label>
                            <input name="title" value="{{ old('title') }}" type="text" placeholder="e.g. Factory Tour &amp; Cleanroom Showcase" required
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-pink-300 outline-none">
                            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-gray-700">Video URL <span class="text-red-500">*</span></label>
                                <template x-if="detectProvider(newVideoUrl)">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                                          :class="detectProvider(newVideoUrl).color">
                                        <i :class="detectProvider(newVideoUrl).icon"></i>
                                        <span x-text="detectProvider(newVideoUrl).name"></span>
                                    </span>
                                </template>
                            </div>
                            <input name="video_url" x-model="newVideoUrl" type="url" placeholder="https://www.youtube.com/watch?v=... or direct .mp4 URL" required
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-pink-300 outline-none">
                            @error('video_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Optional Caption / Description</label>
                        <input name="caption" value="{{ old('caption') }}" type="text" placeholder="e.g. Watch how our robotic test bench guarantees zero defect education hardware."
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-pink-300 outline-none">
                        @error('caption') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <p class="text-[11px] text-gray-500">
                            <i class="fa-solid fa-circle-info text-pink-500 mr-1"></i>
                            Supports YouTube (all formats, shorts &amp; share links), Vimeo, and direct video URLs (<code class="text-gray-700">.mp4</code>, <code class="text-gray-700">.webm</code>, etc.).
                        </p>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 text-sm font-semibold px-5 py-2 rounded-lg text-white transition cursor-pointer shrink-0" style="background:var(--theme-primary)">
                            <i class="fa-solid fa-plus"></i> Add Video
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- ═════════════════ MODALS ═════════════════ --}}

    {{-- Lightbox Fullscreen Photo Modal --}}
    <div x-show="lightbox.open" x-cloak
         class="fixed inset-0 z-[250] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4"
         @keydown.escape.window="lightbox.open = false">
        <div class="relative max-w-4xl w-full flex flex-col items-center" @click.outside="lightbox.open = false">
            <button type="button" @click="lightbox.open = false"
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 text-xl cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="lightbox.src" class="max-h-[80vh] w-auto max-w-full rounded-xl shadow-2xl object-contain" alt="">
            <p x-show="lightbox.caption" x-text="lightbox.caption" class="text-white text-sm mt-3 text-center bg-black/60 px-4 py-1.5 rounded-full"></p>
        </div>
    </div>

    {{-- Edit Photo Caption Modal --}}
    <div x-show="editPhoto.open" x-cloak
         class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @keydown.escape.window="editPhoto.open = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl" @click.outside="editPhoto.open = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Photo Details</h3>
                <button type="button" @click="editPhoto.open = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="flex items-center gap-3 mb-4 p-2 bg-gray-50 rounded-xl">
                <img :src="editPhoto.src" class="w-16 h-16 rounded-lg object-cover border border-gray-200" alt="">
                <p class="text-xs text-gray-500">Update photo caption and descriptive alt text for accessibility and public profile.</p>
            </div>

            <form :action="editPhoto.action" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Caption</label>
                    <input type="text" name="caption" x-model="editPhoto.caption" placeholder="e.g. Cleanroom Assembly Line"
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alt Text (Accessibility)</label>
                    <input type="text" name="alt_text" x-model="editPhoto.alt" placeholder="e.g. Photo of engineers at cleanroom workstation"
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="editPhoto.open = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white rounded-lg cursor-pointer" style="background:var(--theme-primary)">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Watch Video Preview Modal --}}
    <div x-show="previewVideo.open" x-cloak
         class="fixed inset-0 z-[250] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4"
         @keydown.escape.window="previewVideo.open = false">
        <div class="relative max-w-3xl w-full bg-black rounded-2xl overflow-hidden shadow-2xl" @click.outside="previewVideo.open = false">
            <div class="flex items-center justify-between px-4 py-3 bg-gray-900 text-white">
                <span class="text-sm font-bold truncate" x-text="previewVideo.title || 'Video Playback Preview'"></span>
                <button type="button" @click="previewVideo.open = false" class="text-gray-400 hover:text-white cursor-pointer ml-4">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="relative bg-black" style="padding-bottom:56.25%; height:0;">
                <template x-if="previewVideo.open && !previewVideo.isDirect && previewVideo.embedUrl">
                    <iframe :src="previewVideo.embedUrl" class="absolute top-0 left-0 w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </template>
                <template x-if="previewVideo.open && (previewVideo.isDirect || !previewVideo.embedUrl)">
                    <video :src="previewVideo.url" class="absolute top-0 left-0 w-full h-full object-contain" controls autoplay playsinline></video>
                </template>
            </div>
        </div>
    </div>

    {{-- Edit Video Details Modal --}}
    <div x-show="editVideo.open" x-cloak
         class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @keydown.escape.window="editVideo.open = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl" @click.outside="editVideo.open = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Video</h3>
                <button type="button" @click="editVideo.open = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editVideo.action" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Video Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" x-model="editVideo.title" required
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700">Video URL <span class="text-red-500">*</span></label>
                        <template x-if="detectProvider(editVideo.url)">
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                                  :class="detectProvider(editVideo.url).color">
                                <i :class="detectProvider(editVideo.url).icon"></i>
                                <span x-text="detectProvider(editVideo.url).name"></span>
                            </span>
                        </template>
                    </div>
                    <input type="url" name="video_url" x-model="editVideo.url" required
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Caption / Description</label>
                    <input type="text" name="caption" x-model="editVideo.caption"
                           class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="editVideo.open = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white rounded-lg cursor-pointer" style="background:var(--theme-primary)">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

