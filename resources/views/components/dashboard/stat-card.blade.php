@props(['label', 'value', 'icon', 'accent' => 'indigo', 'href' => null])

@if($href)
    <a href="{{ $href }}" class="block bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md hover:border-slate-300 transition-shadow">
        <span class="w-11 h-11 rounded-xl bg-{{ $accent }}-50 text-{{ $accent }}-600 flex items-center justify-center shrink-0">
            <x-dashboard.icon :name="$icon" class="w-5 h-5" />
        </span>
        <div class="min-w-0">
            <p class="text-2xl font-extrabold text-slate-900 font-display leading-tight">{{ $value }}</p>
            <p class="text-xs font-medium text-slate-500 truncate">{{ $label }}</p>
        </div>
    </a>
@else
    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <span class="w-11 h-11 rounded-xl bg-{{ $accent }}-50 text-{{ $accent }}-600 flex items-center justify-center shrink-0">
            <x-dashboard.icon :name="$icon" class="w-5 h-5" />
        </span>
        <div class="min-w-0">
            <p class="text-2xl font-extrabold text-slate-900 font-display leading-tight">{{ $value }}</p>
            <p class="text-xs font-medium text-slate-500 truncate">{{ $label }}</p>
        </div>
    </div>
@endif
