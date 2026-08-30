{{-- Photos & Media + Listing Information sidebar cards. See listing-preview.blade.php for the expected variables. --}}
<x-backend.form-card title="Photos & Media">
    @php($gallery = $listing->getMedia('gallery'))
    @if($gallery->isNotEmpty())
        <div class="grid grid-cols-3 gap-2 mb-3">
            @foreach($gallery as $media)
                <div class="relative">
                    <img src="{{ $media->getUrl() }}" alt="" class="w-full h-16 object-cover rounded-lg border border-gray-200">
                    @if($media->id === $listing->primary_image_media_id)
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white/90 text-amber-500 text-[9px] flex items-center justify-center shadow" title="Main cover photo">
                            <i class="fa-solid fa-star"></i>
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400 mb-3">No photos uploaded yet.</p>
    @endif
    <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="block text-center text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
        Manage Photos in Edit Listing
    </a>
</x-backend.form-card>

<x-backend.form-card title="Listing Information">
    <div class="space-y-2 text-xs">
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Created Date</span>
            <span class="font-medium text-gray-800">{{ $listing->created_at->format('d M Y') }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Published Date</span>
            <span class="font-medium text-gray-800">{{ $listing->published_at ? $listing->published_at->format('d M Y') : 'Not published' }}</span>
        </div>
        @if($listing->rejection_reason)
            <div class="p-2 bg-red-50 text-red-700 rounded-lg text-xs mt-2">
                <strong>Rejection Note:</strong> {{ $listing->rejection_reason }}
            </div>
        @endif
    </div>
</x-backend.form-card>
