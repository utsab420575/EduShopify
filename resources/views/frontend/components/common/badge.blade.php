@props(['variant' => 'neutral'])

@php
    $colors = match ($variant) {
        'verified' => 'bg-emerald-50 text-emerald-600',
        'success' => 'bg-green-50 text-green-800',
        'warning' => 'bg-amber-50 text-amber-800',
        'danger' => 'bg-red-50 text-red-800',
        'info' => 'bg-sky-50 text-sky-800',
        'brand' => 'bg-emerald-500 text-white',
        default => 'bg-gray-100 text-gray-500',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium $colors"]) }}>
    {{ $slot }}
</span>
