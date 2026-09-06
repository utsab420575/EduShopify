{{--
    Hero image + thumbnail strip. Expects $heroFirst (gallery media, primary
    first) and $firstMedia from the parent show.blade.php. `heroUrl` lives in
    the parent's x-data so the tab nav below can share the same Alpine scope.
--}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="relative bg-gray-50 flex items-center justify-center" style="min-height: 320px; max-height: 420px;">
        @if($firstMedia)
            <img :src="heroUrl" alt="{{ $listing->name }}" class="w-full object-contain transition-all duration-300" style="max-height: 420px;">
        @else
            <div class="flex flex-col items-center justify-center py-20 text-gray-300">
                <i class="fa-regular fa-image text-5xl mb-3"></i>
                <p class="text-sm font-medium">No photos available for this listing</p>
            </div>
        @endif
    </div>

    @if($heroFirst->count() > 1)
        <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100 overflow-x-auto">
            @foreach($heroFirst as $media)
                <button type="button"
                        @click="heroUrl = '{{ $media->getUrl() }}'"
                        class="flex-shrink-0 relative w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-150"
                        :class="heroUrl === '{{ $media->getUrl() }}' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-400'">
                    <img src="{{ $media->getUrl() }}" alt="" class="w-full h-full object-cover">
                    @if($media->id === $primaryId)
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-amber-400 flex items-center justify-center shadow">
                            <i class="fa-solid fa-star text-white text-[8px]"></i>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif
</div>
