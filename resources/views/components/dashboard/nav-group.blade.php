@props(['label'])

<div>
    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ $label }}</p>
    <div class="space-y-0.5">
        {{ $slot }}
    </div>
</div>
