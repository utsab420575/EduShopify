@props(['label', 'value', 'hint' => null, 'tone' => 'default', 'icon' => null, 'href' => null])

@php
    $valueTone = [
        'default' => 'text-gray-900',
        'success' => 'text-green-700',
        'warning' => 'text-amber-700',
        'danger'  => 'text-red-600',
        'info'    => 'text-blue-700',
    ][$tone] ?? 'text-gray-900';

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif class="bg-white rounded-xl border border-gray-200 p-4 {{ $href ? 'hover:border-gray-300 transition' : '' }}">
    <div class="flex items-start justify-between">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $label }}</p>
        @if($icon)
            <i class="fa-solid {{ $icon }} text-gray-300"></i>
        @endif
    </div>
    <p class="text-2xl font-bold {{ $valueTone }} mt-2">{{ $value }}</p>
    @if($hint)
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
</{{ $tag }}>
