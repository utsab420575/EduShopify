@props(['icon' => 'fa-inbox', 'title', 'description' => null, 'actionLabel' => null, 'actionUrl' => null])

<div class="flex flex-col items-center justify-center text-center py-16 px-4">
    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4" style="background:var(--fe-surface-soft);">
        <i class="fa-solid {{ $icon }} text-xl" style="color:var(--fe-text-subtle);"></i>
    </div>
    <p class="text-base font-semibold" style="color:var(--fe-text);">{{ $title }}</p>
    @if($description)
        <p class="text-sm mt-1 max-w-sm" style="color:var(--fe-text-muted);">{{ $description }}</p>
    @endif
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="mt-4 text-sm font-semibold" style="color:var(--fe-primary);">{{ $actionLabel }}</a>
    @endif
</div>
