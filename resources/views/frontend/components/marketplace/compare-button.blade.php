@props(['listing', 'variant' => null, 'style' => 'icon'])

{{--
    "Add to Compare" button — supports 'icon' (floating on cards) and 'text' (on PDP).
    Purely client-side localStorage state via EdushopifyCompare.
--}}
<button
    type="button"
    x-data="compareButton({{ (int) $listing->id }}, {{ $variant?->id ? (int) $variant->id : 'null' }})"
    @click.prevent.stop="toggle()"
    :aria-pressed="active.toString()"
    :title="active ? 'Remove from comparison' : 'Add to compare'"
    @if($style === 'icon')
        class="group fe-focus-ring w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 ease-in-out shrink-0 cursor-pointer shadow-xs"
        :class="active
            ? 'bg-emerald-600 text-white border border-emerald-600 hover:bg-rose-600 hover:border-rose-600 hover:text-white hover:scale-110 hover:shadow-md'
            : 'bg-white/95 text-slate-500 border border-slate-200/80 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 hover:scale-110 hover:shadow-md backdrop-blur-xs'"
    @else
        class="group fe-focus-ring block w-full text-center px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 ease-in-out cursor-pointer"
        :class="active
            ? 'bg-emerald-50 text-emerald-700 border border-emerald-400 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 font-semibold shadow-xs'
            : 'bg-white text-slate-600 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 hover:shadow-xs'"
    @endif
>
    @if($style === 'icon')
        <i class="fa-solid fa-arrow-right-arrow-left text-xs transition-transform duration-200 group-hover:rotate-180"></i>
    @else
        <i class="fa-solid fa-arrow-right-arrow-left mr-1.5 transition-transform duration-200 group-hover:scale-110"></i>
        <span x-text="active ? 'Remove from Compare' : 'Add to Compare'"></span>
    @endif
</button>

