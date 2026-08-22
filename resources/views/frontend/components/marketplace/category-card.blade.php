@props(['category'])

<a href="{{ route('frontend.categories.show', $category->slug) }}" class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col items-center text-center gap-3">
    <span class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
        <i class="fa-solid {{ $category->icon ?: 'fa-shapes' }} text-lg"></i>
    </span>
    <div>
        <p class="text-sm font-semibold" style="color:var(--fe-text);">{{ $category->name }}</p>
        @if(isset($category->public_listing_count))
            <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">{{ $category->public_listing_count }} {{ Str::plural('listing', $category->public_listing_count) }}</p>
        @endif
    </div>
</a>
