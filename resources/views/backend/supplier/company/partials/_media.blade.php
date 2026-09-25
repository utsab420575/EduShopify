{{-- ── 3. Media & Branding ── --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        /* Banner crop box: width represents the hero section's fixed
           horizontal framing (not user-resizable), height is the only
           dimension the supplier can drag taller/shorter. Hides every
           Cropper.js handle except the top/bottom (n/s) ones. */
        .vertical-only-crop .cropper-point.point-e,
        .vertical-only-crop .cropper-point.point-w,
        .vertical-only-crop .cropper-point.point-ne,
        .vertical-only-crop .cropper-point.point-nw,
        .vertical-only-crop .cropper-point.point-se,
        .vertical-only-crop .cropper-point.point-sw,
        .vertical-only-crop .cropper-line.line-e,
        .vertical-only-crop .cropper-line.line-w {
            display: none !important;
        }
    </style>
@endpush

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

            <div x-data="bannerCropper()" x-init="init()">
                <label class="block text-sm font-medium text-gray-700 mb-3">Hero Banner Image <span class="text-gray-400 font-normal">(wide banner, ~4.4:1 ratio, max 4MB)</span></label>

                <img id="banner-preview" src="{{ $profile?->banner ? asset('storage/'.$profile->banner) : '' }}"
                     class="w-full rounded-lg object-cover bg-gray-100 border border-gray-200" style="aspect-ratio:22/5; {{ $profile?->banner ? '' : 'display:none' }}" alt="Banner">
                <div id="banner-placeholder" class="w-full rounded-lg bg-gray-50 border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 py-8" style="aspect-ratio:22/5; {{ $profile?->banner ? 'display:none' : '' }}">
                    <i class="fa-solid fa-image text-2xl mb-1.5"></i>
                    <p class="text-xs">No banner uploaded yet</p>
                </div>

                <div class="flex items-center gap-3 mt-3">
                    <button type="button" @click="$refs.bannerPicker.click()"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-2 rounded-lg bg-violet-50 text-violet-700 hover:bg-violet-100 transition cursor-pointer">
                        <i class="fa-solid fa-crop-simple"></i>
                        {{ $profile?->banner ? 'Change & Crop Banner' : 'Upload & Crop Banner' }}
                    </button>
                    <p class="text-[11px] text-gray-400">Shown at the top of your public profile.</p>
                </div>
                <input name="banner" type="file" accept="image/*" x-ref="bannerPicker" @change="onFileSelected($event)" class="hidden">
                @error('banner') <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p> @enderror

                {{-- Crop modal --}}
                <div x-show="modalOpen" x-cloak x-transition.opacity
                     class="fixed inset-0 z-[200] bg-black/70 flex items-center justify-center sm:p-4">
                    <div class="bg-white w-full h-full sm:h-auto sm:max-h-[92vh] sm:max-w-2xl sm:rounded-2xl flex flex-col overflow-hidden">

                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Crop Hero Banner</p>
                                <p class="text-xs text-gray-400 mt-0.5">Drag the top/bottom handles to make it taller or shorter, drag the image to reposition, use the slider to zoom.</p>
                            </div>
                            <button type="button" @click="cancelCrop()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="flex-1 min-h-0 overflow-y-auto px-5 py-4">
                            <div class="w-full bg-gray-900 rounded-lg overflow-hidden vertical-only-crop" style="height:min(48vh,380px)">
                                <img id="banner-crop-image" :src="rawImageSrc" style="display:block;max-width:100%;">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-2">
                                <i class="fa-solid fa-arrows-up-down mr-1"></i>
                                Width is fixed to match the hero section; only height is adjustable.
                            </p>

                            <div class="flex items-center gap-3 mt-4">
                                <i class="fa-solid fa-magnifying-glass-minus text-gray-400 text-xs"></i>
                                <input type="range" min="0" max="100" x-model.number="zoomSlider" @input="handleZoomSlider()"
                                       class="flex-1 accent-violet-600 cursor-pointer">
                                <i class="fa-solid fa-magnifying-glass-plus text-gray-400 text-xs"></i>
                                <button type="button" @click="resetCrop()" class="text-xs font-medium text-gray-500 hover:text-gray-700 ml-2 cursor-pointer shrink-0">Reset</button>
                            </div>

                            <div class="mt-5">
                                <p class="text-xs font-medium text-gray-500 mb-2">Hero Section Preview <span class="text-gray-400 font-normal">— taller crops are center-fit into this area, same as the live page</span></p>
                                <div class="rounded-lg overflow-hidden border border-gray-200 relative bg-gray-100 mx-auto" style="aspect-ratio:22/5; max-width:380px;">
                                    <img class="banner-crop-live-preview absolute inset-0 w-full h-full" style="object-fit:cover;object-position:center;" alt="">
                                    <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(to top,rgba(0,0,0,.65) 0%,rgba(0,0,0,.2) 55%,rgba(0,0,0,0) 100%)"></div>
                                    <div class="absolute left-3 bottom-2 flex items-center gap-1.5 pointer-events-none">
                                        <div class="w-5 h-5 rounded bg-white/90 flex items-center justify-center text-[9px] font-bold text-gray-700 shrink-0">{{ strtoupper(substr($profile?->display_name ?? 'S', 0, 1)) }}</div>
                                        <p class="text-white text-[11px] font-semibold truncate">{{ $profile?->display_name ?? 'Your Company' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-100 shrink-0">
                            <button type="button" @click="cancelCrop()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Cancel</button>
                            <button type="button" @click="confirmCrop()" class="inline-flex items-center gap-1.5 text-sm font-medium px-5 py-2 rounded-lg text-white cursor-pointer" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-check"></i> Use This Crop
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Media
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        // Client-side crop for the supplier public profile's hero banner
        // (.hero-banner, frontend_new.css). Width is locked to the hero's
        // horizontal framing; height is deliberately NOT locked via Cropper's
        // aspectRatio — the supplier can drag the crop box taller or shorter
        // than the hero's 280px display height (only the n/s handles are
        // left enabled, see .vertical-only-crop CSS above). Whatever height
        // they pick, .hero-banner img's own object-fit:cover center-fits it
        // at render time — the "Hero Section Preview" box below mirrors that
        // exact behavior (object-fit:cover on a fixed 22:5 box) so what the
        // supplier sees while cropping matches production.
        function bannerCropper() {
            return {
                modalOpen: false,
                cropper: null,
                zoomSlider: 50,
                rawImageSrc: null,
                aspectRatio: 22 / 5,
                cropWidth: 1760,
                previewPending: false,

                init() {},

                onFileSelected(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.rawImageSrc = e.target.result;
                        this.modalOpen = true;
                        this.zoomSlider = 50;
                        this.$nextTick(() => this.initCropper());
                    };
                    reader.readAsDataURL(file);
                },

                initCropper() {
                    const image = document.getElementById('banner-crop-image');
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    this.cropper = new Cropper(image, {
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        background: false,
                        responsive: true,
                        zoomOnWheel: true,
                        ready: () => {
                            // Start at the hero's 22:5 ratio; height is then
                            // freely draggable from this starting point via
                            // the n/s handles.
                            const container = this.cropper.getContainerData();
                            const width = container.width;
                            const height = width / this.aspectRatio;
                            this.cropper.setCropBoxData({
                                left: 0,
                                top: Math.max(0, (container.height - height) / 2),
                                width,
                                height,
                            });
                            this.updateLivePreview();
                        },
                        crop: () => this.queuePreviewUpdate(),
                        zoom: () => this.queuePreviewUpdate(),
                    });
                },

                queuePreviewUpdate() {
                    if (this.previewPending) return;
                    this.previewPending = true;
                    requestAnimationFrame(() => {
                        this.updateLivePreview();
                        this.previewPending = false;
                    });
                },

                updateLivePreview() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas({ width: 380 });
                    if (!canvas) return;
                    const preview = document.querySelector('.banner-crop-live-preview');
                    if (preview) preview.src = canvas.toDataURL('image/jpeg', 0.85);
                },

                handleZoomSlider() {
                    if (!this.cropper) return;
                    const delta = (this.zoomSlider - (this._lastZoom ?? 50)) / 100;
                    this.cropper.zoom(delta);
                    this._lastZoom = this.zoomSlider;
                },

                resetCrop() {
                    if (!this.cropper) return;
                    this.cropper.reset();
                    const container = this.cropper.getContainerData();
                    const width = container.width;
                    const height = width / this.aspectRatio;
                    this.cropper.setCropBoxData({
                        left: 0,
                        top: Math.max(0, (container.height - height) / 2),
                        width,
                        height,
                    });
                    this.zoomSlider = 50;
                    this._lastZoom = 50;
                    this.updateLivePreview();
                },

                cancelCrop() {
                    this.closeModal();
                    this.$refs.bannerPicker.value = '';
                },

                confirmCrop() {
                    if (!this.cropper) return;

                    // Only width is forced — height is whatever the supplier
                    // dragged the crop box to, scaled proportionally.
                    const canvas = this.cropper.getCroppedCanvas({
                        width: this.cropWidth,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob((blob) => {
                        const file = new File([blob], 'hero-banner.jpg', { type: 'image/jpeg' });
                        const transfer = new DataTransfer();
                        transfer.items.add(file);
                        this.$refs.bannerPicker.files = transfer.files;

                        const preview = document.getElementById('banner-preview');
                        const placeholder = document.getElementById('banner-placeholder');
                        preview.src = canvas.toDataURL('image/jpeg', 0.92);
                        preview.style.display = 'block';
                        if (placeholder) placeholder.style.display = 'none';

                        this.closeModal();
                    }, 'image/jpeg', 0.92);
                },

                closeModal() {
                    this.modalOpen = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                },
            };
        }
    </script>
@endpush
