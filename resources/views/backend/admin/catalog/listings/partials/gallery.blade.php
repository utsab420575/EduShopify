{{-- Photos & Media Gallery — thumbnails link into the shared .listing-lightbox
     :target panels (CSS defined once in _panel.blade.php). See that file for
     why this is CSS-only rather than JS, and for the expected variables. --}}
<x-backend.form-card title="Photos & Media Gallery">
    @php($gallery = $listing->getMedia('gallery'))
    @if($gallery->isNotEmpty())
        {{-- Capped height with its own scroll — a listing with many photos
             shouldn't blow up the tab's overall height. --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-h-80 overflow-y-auto pr-1">
            @foreach($gallery as $idx => $media)
                <a href="#listing-photo-{{ $media->id }}" class="group relative aspect-square rounded-xl border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center hover:ring-2 hover:ring-indigo-500 transition-all">
                    <img src="{{ $media->getUrl() }}" alt="{{ $listing->name }}" class="w-full h-full object-contain p-1">
                    @if($idx === 0)
                        <span class="absolute top-1.5 left-1.5 px-2 py-0.5 bg-indigo-600/90 text-white text-[10px] font-bold rounded-md shadow-xs">Primary</span>
                    @endif
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass-plus text-white text-sm opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>

                {{-- Lightbox panel for this photo --}}
                <div id="listing-photo-{{ $media->id }}" class="listing-lightbox fixed inset-0 z-50 items-center justify-center p-4">
                    <a href="#" class="absolute inset-0 bg-gray-900/70" aria-label="Close"></a>
                    <div class="relative max-w-3xl w-full">
                        <a href="#" class="absolute -top-9 right-0 text-white/80 hover:text-white text-sm" aria-label="Close"><i class="fa-solid fa-xmark text-lg"></i></a>
                        <img src="{{ $media->getUrl() }}" alt="{{ $listing->name }}" class="w-full max-h-[80vh] object-contain rounded-xl bg-white">
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="py-6 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <i class="fa-solid fa-image text-3xl mb-1.5"></i>
            <p class="text-xs">No media uploaded for this listing.</p>
        </div>
    @endif
</x-backend.form-card>
