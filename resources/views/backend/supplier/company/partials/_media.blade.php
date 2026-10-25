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

<div id="sp-section-media" data-profile-section="Media &amp; Branding" data-filled="{{ (filled($profile?->logo)) ? 1 : 0 }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('media', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-media', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                <i class="fa-solid fa-image text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Media & Branding</p>
                <p class="text-xs text-gray-400">Logo, banner, profile photo</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="{{ supplier_route('company.profile.media.update') }}" enctype="multipart/form-data">
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
                            <input name="logo" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                                   onchange="spPickImage(this, { label: 'Logo', maxMb: 2, previewId: 'logo-preview' })"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Profile Photo <span class="text-gray-400 font-normal">(max 2MB)</span></label>
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
                            <input name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                                   onchange="spPickImage(this, { label: 'Profile photo', maxMb: 2, previewId: 'photo-preview', placeholderId: 'photo-placeholder' })"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                            @error('profile_photo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            @php
                // Re-crop source: the stored original, or — for a banner saved
                // before originals were kept — the banner itself.
                $bannerSource = $profile?->banner_original ?: $profile?->banner;
            @endphp
            <div x-data="bannerCropper({
                     sourceUrl: @js($bannerSource ? asset('storage/'.$bannerSource) : null),
                     bannerUrl: @js($profile?->banner ? asset('storage/'.$profile->banner) : null),
                     crop: @js($profile?->banner_crop),
                     fit: @js($profile?->banner_fit ?: 'cover'),
                 })">
                <label class="block text-sm font-medium text-gray-700 mb-3">Hero Banner Image <span class="text-gray-400 font-normal">(wide banner, ~4.4:1 ratio, images up to 20 MB)</span></label>

                {{-- Live previews of the public hero on desktop and on a phone --}}
                <div x-show="previewSrc" x-cloak class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-gray-500 mb-1"><i class="fa-solid fa-desktop mr-1"></i> Desktop</p>
                        <div class="relative group cursor-pointer" @click="openRecrop()" title="Click to adjust the crop">
                            @include('backend.supplier.company.partials._banner-hero-preview', ['src' => 'previewSrc', 'ratio' => '1232/300'])
                            <div x-show="sourceSrc"
                                 class="absolute inset-0 rounded-lg bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center pointer-events-none">
                                <span class="opacity-0 group-hover:opacity-100 transition inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-black/50 px-3 py-1.5 rounded-full">
                                    <i class="fa-solid fa-crop-simple"></i> Adjust crop
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="w-36 shrink-0">
                        <p class="text-[11px] font-medium text-gray-500 mb-1"><i class="fa-solid fa-mobile-screen mr-1"></i> Mobile</p>
                        @include('backend.supplier.company.partials._banner-hero-preview', ['src' => 'previewSrc', 'ratio' => '375/300'])
                    </div>
                </div>
                <div x-show="!previewSrc" class="w-full rounded-lg bg-gray-50 border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 py-8" style="aspect-ratio:1232/300;">
                    <i class="fa-solid fa-image text-2xl mb-1.5"></i>
                    <p class="text-xs">No banner uploaded yet</p>
                </div>

                <div x-show="previewSrc" x-cloak class="mt-4">
                    @include('backend.supplier.company.partials._banner-fit-picker')
                </div>
                <input type="hidden" name="banner_fit" :value="fit">

                <div class="flex flex-wrap items-center gap-3 mt-3">
                    <button type="button" @click="$refs.bannerPicker.click()"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-2 rounded-lg bg-violet-50 text-violet-700 hover:bg-violet-100 transition cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        {{ $profile?->banner ? 'Change Banner' : 'Upload & Crop Banner' }}
                    </button>
                    <button type="button" x-show="sourceSrc" x-cloak @click="openRecrop()"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        <i class="fa-solid fa-crop-simple"></i> Adjust Crop
                    </button>
                    <p class="text-[11px] text-gray-400" x-show="!cropDirty && fit === initialFit">Shown at the top of your public profile.</p>
                    <p class="text-[11px] font-medium text-amber-600" x-show="cropDirty || fit !== initialFit" x-cloak>
                        <i class="fa-solid fa-circle-info"></i> Unsaved banner changes — click Save Media to apply them.
                    </p>
                </div>
                <input name="banner" type="file" accept="image/jpeg,image/png,image/webp,image/gif" x-ref="bannerPicker" @change="onFileSelected($event)" class="hidden">

                {{-- Crop box (original-image pixels), sent only once a crop was applied in this visit --}}
                <template x-if="cropDirty && appliedCrop">
                    <div>
                        <input type="hidden" name="banner_crop[x]" :value="appliedCrop.x">
                        <input type="hidden" name="banner_crop[y]" :value="appliedCrop.y">
                        <input type="hidden" name="banner_crop[width]" :value="appliedCrop.width">
                        <input type="hidden" name="banner_crop[height]" :value="appliedCrop.height">
                    </div>
                </template>

                @error('banner') <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p> @enderror
                @error('banner_crop.*') <p class="text-xs text-red-600 mt-1.5">The crop area was invalid — please crop the banner again.</p> @enderror

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
                                <div x-show="preparing" class="w-full h-full flex items-center justify-center text-gray-300 text-sm">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Preparing image…
                                </div>
                                <img id="banner-crop-image" :src="rawImageSrc" x-show="!preparing" style="display:block;max-width:100%;">
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
                                @include('backend.supplier.company.partials._banner-fit-picker')
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-medium text-gray-500 mb-2">Hero Section Preview <span class="text-gray-400 font-normal">— exactly how your public profile will show it</span></p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-medium text-gray-500 mb-1"><i class="fa-solid fa-desktop mr-1"></i> Desktop</p>
                                        @include('backend.supplier.company.partials._banner-hero-preview', ['src' => 'livePreviewSrc', 'ratio' => '1232/300'])
                                    </div>
                                    <div class="w-32 shrink-0">
                                        <p class="text-[11px] font-medium text-gray-500 mb-1"><i class="fa-solid fa-mobile-screen mr-1"></i> Mobile</p>
                                        @include('backend.supplier.company.partials._banner-hero-preview', ['src' => 'livePreviewSrc', 'ratio' => '375/300'])
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
        // Rejects a picked file that isn't a previewable image or is over the
        // size limit (same limits as UpdateMediaRequest), clearing the input
        // so the over-size file is never submitted.
        function spCheckImage(input, file, label, maxMb) {
            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            let message = null;

            if (!allowed.includes(file.type)) {
                message = 'Please choose a JPG, PNG, WebP or GIF image.';
            } else if (file.size > maxMb * 1024 * 1024) {
                message = `${label} size (${(file.size / 1024 / 1024).toFixed(1)} MB) exceeds the ${maxMb} MB limit. Please choose a smaller image.`;
            }

            if (!message) return true;

            input.value = '';
            Swal.fire({ icon: 'error', title: message.startsWith('Please') ? 'Unsupported image' : 'Image too large', text: message, confirmButtonColor: '#4f46e5' });
            return false;
        }

        // Logo / profile photo: validate, then show the chosen image
        // in place of the current one straight away.
        function spPickImage(input, { label, maxMb, previewId, placeholderId = null }) {
            const file = input.files && input.files[0];
            if (!file || !spCheckImage(input, file, label, maxMb)) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                const placeholder = placeholderId && document.getElementById(placeholderId);
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        // Shrinks a picked image to ≤maxSide px and re-encodes it as JPEG,
        // stepping quality down until it fits the server's 4 MB limit. The
        // result is both what the cropper shows and what gets uploaded, so
        // the crop coordinates refer to exactly the image the server crops.
        async function spShrinkImage(file, maxSide = 3000, maxBytes = 4 * 1024 * 1024) {
            const url = URL.createObjectURL(file);
            try {
                const img = await new Promise((resolve, reject) => {
                    const i = new Image();
                    i.onload = () => resolve(i);
                    i.onerror = reject;
                    i.src = url;
                });
                const scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(img.naturalWidth * scale);
                canvas.height = Math.round(img.naturalHeight * scale);
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';   // transparent PNG areas → white, not black
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                let blob = null;
                for (const quality of [0.9, 0.8, 0.7, 0.6]) {
                    blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
                    if (blob && blob.size <= maxBytes) break;
                }
                return blob;
            } finally {
                URL.revokeObjectURL(url);
            }
        }

        // Hero banner crop (.hero-banner, frontend_new.css). Cropper.js only
        // CHOOSES the area here — the actual crop is done server-side by
        // Intervention (SupplierProfileService::updateBanner) from the
        // uploaded/stored original, using the box sent as banner_crop[*] in
        // original-image pixels. Width is locked to the hero's horizontal
        // framing; height is draggable (only the n/s handles are enabled,
        // see .vertical-only-crop CSS above), and .hero-banner img's own
        // object-fit:cover center-fits whatever height is picked — the "Hero
        // Section Preview" box mirrors that exact behaviour.
        function bannerCropper({ sourceUrl = null, bannerUrl = null, crop = null, fit = 'cover' } = {}) {
            return {
                // How the banner fills the hero (cover/contain/fill) and the
                // images the page / modal previews show.
                fit: fit,
                initialFit: fit,
                previewSrc: bannerUrl,
                livePreviewSrc: null,

                modalOpen: false,
                preparing: false,
                cropper: null,
                zoomSlider: 50,
                rawImageSrc: null,
                aspectRatio: 1232 / 300,
                cropWidth: 1760,
                previewPending: false,

                // Image the current crop refers to (stored original, or a newly
                // picked + shrunk file not saved yet) and the crop applied to it.
                sourceSrc: sourceUrl,
                appliedCrop: crop,
                cropDirty: false,

                // A pick in progress (shown in the modal, not yet confirmed),
                // and the last confirmed file — restored on Cancel.
                pendingFile: null,
                pendingSrc: null,
                confirmedFile: null,
                restoreCropData: null,

                async onFileSelected(event) {
                    const input = event.target;
                    const file = input.files && input.files[0];
                    if (!file) return;
                    if (!spCheckImage(input, file, 'Banner', 20)) {
                        this.restoreConfirmedFile();
                        return;
                    }

                    this.preparing = true;
                    this.modalOpen = true;
                    try {
                        const blob = await spShrinkImage(file);
                        if (!blob) throw new Error('shrink failed');
                        this.pendingFile = new File([blob], 'hero-banner.jpg', { type: 'image/jpeg' });
                        this.pendingSrc = URL.createObjectURL(blob);
                    } catch (e) {
                        this.preparing = false;
                        this.modalOpen = false;
                        this.restoreConfirmedFile();
                        Swal.fire({ icon: 'error', title: 'Unsupported image', text: 'This image could not be opened. Please try a different JPG, PNG or WebP file.', confirmButtonColor: '#4f46e5' });
                        return;
                    }

                    this.preparing = false;
                    this.openCropper(this.pendingSrc, null);
                },

                // Click on the banner / "Adjust Crop": reopen the cropper on the
                // image the current crop came from, with that crop restored.
                openRecrop() {
                    if (!this.sourceSrc) return;
                    this.pendingFile = null;
                    this.pendingSrc = null;
                    this.openCropper(this.sourceSrc, this.appliedCrop);
                },

                openCropper(src, restoreCrop) {
                    this.restoreCropData = restoreCrop;
                    this.livePreviewSrc = null;
                    this.rawImageSrc = src;
                    this.modalOpen = true;
                    this.zoomSlider = 50;
                    this._lastZoom = 50;
                    this.$nextTick(() => this.initCropper());
                },

                restoreConfirmedFile() {
                    const transfer = new DataTransfer();
                    if (this.confirmedFile) transfer.items.add(this.confirmedFile);
                    this.$refs.bannerPicker.files = transfer.files;
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
                        // Sources are canvas/GD re-encodes with no EXIF, so
                        // pixel coordinates already match what the server reads.
                        checkOrientation: false,
                        ready: () => {
                            // Re-crop: put the previous box back exactly.
                            if (this.restoreCropData) {
                                this.cropper.setData(this.restoreCropData);
                                this.updateLivePreview();
                                return;
                            }
                            // Start at the hero's 1232:300 ratio; height is then
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
                    const canvas = this.cropper.getCroppedCanvas({ width: 600 });
                    if (!canvas) return;
                    this.livePreviewSrc = canvas.toDataURL('image/jpeg', 0.85);
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

                // Cancel drops only the pick in progress; an earlier confirmed
                // file/crop (or the saved banner) stays as it was.
                cancelCrop() {
                    this.closeModal();
                    if (this.pendingSrc) URL.revokeObjectURL(this.pendingSrc);
                    this.pendingFile = null;
                    this.pendingSrc = null;
                    this.restoreConfirmedFile();
                },

                confirmCrop() {
                    if (!this.cropper) return;

                    // Box in the source image's own pixels — the server crops
                    // the same image with Intervention.
                    const data = this.cropper.getData(true);
                    this.appliedCrop = { x: data.x, y: data.y, width: data.width, height: data.height };
                    this.cropDirty = true;

                    // A newly picked image becomes the file to upload and the
                    // source for any further re-crop before saving.
                    if (this.pendingFile) {
                        this.confirmedFile = this.pendingFile;
                        this.sourceSrc = this.pendingSrc;
                        this.pendingFile = null;
                        this.pendingSrc = null;
                    }
                    this.restoreConfirmedFile();

                    // Instant on-page preview only; the saved banner is the
                    // server's crop of the same area.
                    const canvas = this.cropper.getCroppedCanvas({
                        width: this.cropWidth,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });
                    if (canvas) this.previewSrc = canvas.toDataURL('image/jpeg', 0.85);

                    this.closeModal();
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
