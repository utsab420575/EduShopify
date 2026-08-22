@props([
    'label',
    'icon',
    'route' => null,
    'routePattern' => null,
    'accent' => 'indigo',
    'disabled' => false,
    'badge' => null,
])

@php
    // A nav item is only ever a real link when its route is both given AND
    // registered — this lets sidebar entries for not-yet-built screens stay
    // "Soon" automatically instead of needing a manually-flipped disabled prop.
    $routeExists = $route && \Illuminate\Support\Facades\Route::has($route);
    $disabled = $disabled || ! $routeExists;

    $active = false;
    if (! $disabled) {
        $active = request()->routeIs($routePattern ?? $route . '*');
    }

    $classes = $disabled
        ? 'text-slate-400 cursor-not-allowed'
        : ($active
            ? "bg-{$accent}-50 text-{$accent}-700 font-semibold"
            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900');
@endphp

@if($disabled)
    <span class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ $classes }}">
        <x-dashboard.icon :name="$icon" class="w-5 h-5 shrink-0" />
        <span class="flex-1">{{ $label }}</span>
        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-300 bg-slate-100 px-1.5 py-0.5 rounded">Soon</span>
    </span>
@else
    <a href="{{ route($route) }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors {{ $classes }}">
        <x-dashboard.icon :name="$icon" class="w-5 h-5 shrink-0 {{ $active ? "text-{$accent}-600" : 'text-slate-400 group-hover:text-slate-600' }}" />
        <span class="flex-1">{{ $label }}</span>
        @if($badge)
            <span class="text-[11px] font-semibold text-white bg-{{ $accent }}-500 rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">{{ $badge }}</span>
        @endif
    </a>
@endif
