{{--
    Mirrors the public hero (.hero-banner, frontend_new/suppliers/show.blade.php):
    same object-fit per `fit`, and for "contain" the same blurred copy behind
    the image. Used inside bannerCropper()'s x-data — `fit` is its state.

    $src   — Alpine expression for the image URL
    $ratio — box shape: desktop hero 1232/300 (≈1232×300), phone 375/300
--}}
<div class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-100" style="aspect-ratio: {{ $ratio }};">
    <template x-if="{{ $src }}">
        <div class="absolute inset-0">
            <img x-show="fit === 'contain'" :src="{{ $src }}" alt="" aria-hidden="true"
                 class="absolute inset-0 w-full h-full" style="object-fit:cover;filter:blur(12px) brightness(.75);transform:scale(1.15);">
            <img :src="{{ $src }}" alt="" class="absolute inset-0 w-full h-full" :style="`object-fit:${fit}`">
        </div>
    </template>
    <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(to top,rgba(0,0,0,.65) 0%,rgba(0,0,0,.2) 55%,rgba(0,0,0,0) 100%)"></div>
    <div class="absolute left-2.5 bottom-2 right-2 flex items-center gap-1.5 pointer-events-none">
        <div class="w-5 h-5 rounded bg-white/90 flex items-center justify-center text-[9px] font-bold text-gray-700 shrink-0">{{ strtoupper(substr($profile?->display_name ?? 'S', 0, 1)) }}</div>
        <p class="text-white text-[11px] font-semibold truncate">{{ $profile?->display_name ?? 'Your Company' }}</p>
    </div>
</div>
