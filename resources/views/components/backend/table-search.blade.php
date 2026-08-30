{{--
    Table toolbar per design.md §9 (search) and §6 (Filters button styling —
    sliders icon, same border/hover classes as the page-header "Filters"
    button). Both the search input and the filter field auto-submit — search
    debounced 500ms after typing stops, the filter field on change, same as
    the live-select pattern already used by units/attributes/categories index
    pages — so neither needs an explicit Search/Apply click. One <form>
    covers both so submitting either carries the other's current value along.
--}}
@props([
    'title', 'count', 'searchParam', 'pageParam', 'currentSearch', 'placeholder' => 'Search...',
    'filterParams' => [], 'hasActiveFilter' => false,
])
<div x-data="{ filterOpen: {{ $hasActiveFilter ? 'true' : 'false' }} }" class="relative">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-gray-900">{{ $title }}</h3>
            <span class="text-xs text-gray-400">{{ $count }} total</span>
        </div>

        <form method="GET" class="flex items-center gap-2">
            @foreach(request()->except(array_merge([$searchParam, $pageParam], $filterParams)) as $key => $value)
                @if(!is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="{{ $searchParam }}" value="{{ $currentSearch }}" placeholder="{{ $placeholder }}"
                       @input.debounce.500ms="$event.target.form.requestSubmit()"
                       x-init="if (new URLSearchParams(window.location.search).get('{{ $searchParam }}')) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }"
                       class="focus-accent w-full sm:w-56 pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
            </div>

            @isset($filters)
                <button type="button" @click="filterOpen = !filterOpen"
                        class="relative text-sm font-medium px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-sliders text-xs"></i> Filters
                    @if($hasActiveFilter)
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full" style="background:var(--theme-primary)"></span>
                    @endif
                </button>
            @endisset

            @isset($filters)
                <div x-show="filterOpen" @click.outside="filterOpen = false" x-transition x-cloak
                     class="absolute z-20 top-full mt-1.5 right-5 bg-white border border-gray-200 rounded-lg shadow-lg p-4 flex items-end gap-3 whitespace-nowrap">
                    {{ $filters }}
                </div>
            @endisset
        </form>
    </div>
</div>
