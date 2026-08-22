@props(['variant' => 'neutral'])

@php
    $styles = match ($variant) {
        'verified' => 'background:var(--fe-primary-soft);color:var(--fe-primary);',
        'success' => 'background:var(--fe-success-soft);color:#166534;',
        'warning' => 'background:var(--fe-warning-soft);color:#92400e;',
        'danger' => 'background:var(--fe-danger-soft);color:#991b1b;',
        'info' => 'background:var(--fe-info-soft);color:#075985;',
        'brand' => 'background:var(--fe-primary);color:#fff;',
        default => 'background:var(--fe-surface-soft);color:var(--fe-text-muted);',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium']) }} style="{{ $styles }}">
    {{ $slot }}
</span>
