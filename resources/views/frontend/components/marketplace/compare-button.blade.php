@props(['listing', 'variant' => null, 'style' => 'icon'])

{{--
    "Add to Compare" — appears on every card surface (home/search/category/
    related listings all render through listing-card.blade.php, so wiring
    it there covers all of them in one place) and on the listing detail
    page. Guest-accessible, no auth check anywhere (spec §56) — purely
    client-side localStorage state via EdushopifyCompare
    (resources/js/frontend/comparison.js), no page reload, no server write.
--}}
<button
    type="button"
    x-data="compareButton({{ (int) $listing->id }}, {{ $variant?->id ? (int) $variant->id : 'null' }})"
    @click.prevent.stop="toggle()"
    :aria-pressed="active.toString()"
    :title="active ? 'Remove from comparison' : 'Add to compare'"
    @if($style === 'icon')
        class="fe-focus-ring w-9 h-9 rounded-full flex items-center justify-center transition-colors shrink-0"
        :style="active ? 'background:var(--fe-primary);color:#fff;' : 'background:rgba(255,255,255,.92);color:var(--fe-text-muted);'"
    @else
        class="fe-focus-ring block w-full text-center px-4 py-2 rounded-xl text-sm font-medium transition-colors hover:opacity-80"
        style="color:var(--fe-text-muted);"
    @endif
>
    @if($style === 'icon')
        <i class="fa-solid fa-arrow-right-arrow-left text-xs"></i>
    @else
        <i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i>
        <span x-text="active ? 'Remove from Compare' : 'Add to Compare'"></span>
    @endif
</button>
