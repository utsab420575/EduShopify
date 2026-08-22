@props(['eyebrow' => null, 'title', 'subtitle' => null, 'action' => null, 'actionLabel' => null, 'align' => 'left'])

<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 {{ $align === 'center' ? 'text-center sm:text-left' : '' }}">
    <div>
        @if($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:var(--fe-primary);">{{ $eyebrow }}</p>
        @endif
        <h2 class="text-2xl sm:text-[26px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">{{ $title }}</h2>
        @if($subtitle)
            <p class="mt-1.5 text-sm sm:text-base" style="color:var(--fe-text-muted);">{{ $subtitle }}</p>
        @endif
    </div>
    @if($action && $actionLabel)
        <a href="{{ $action }}" class="fe-focus-ring shrink-0 text-sm font-semibold inline-flex items-center gap-1.5" style="color:var(--fe-primary);">
            {{ $actionLabel }} <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    @endif
</div>
