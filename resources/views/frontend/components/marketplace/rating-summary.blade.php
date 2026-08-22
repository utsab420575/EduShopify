@props(['rating' => null, 'count' => 0, 'size' => 'compact'])

@if($rating > 0 && $count > 0)
    @if($size === 'compact')
        <span class="inline-flex items-center gap-1 text-sm">
            <i class="fa-solid fa-star text-xs" style="color:var(--fe-rating);"></i>
            <span class="font-semibold" style="color:var(--fe-text);">{{ number_format($rating, 1) }}</span>
            <span style="color:var(--fe-text-muted);">({{ $count }})</span>
        </span>
    @else
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-0.5">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star text-sm" style="color:{{ $i <= round($rating) ? 'var(--fe-rating)' : '#E2E8F0' }};"></i>
                @endfor
            </div>
            <span class="text-sm font-semibold" style="color:var(--fe-text);">{{ number_format($rating, 1) }}</span>
            <span class="text-sm" style="color:var(--fe-text-muted);">({{ $count }} {{ Str::plural('review', $count) }})</span>
        </div>
    @endif
@else
    <span class="text-xs" style="color:var(--fe-text-subtle);">No reviews yet</span>
@endif
