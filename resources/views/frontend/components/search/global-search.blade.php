@props(['size' => 'default'])

<div x-data="globalSearch" class="relative" @keydown.escape="closeOnEscape()">
    <form method="GET" action="{{ route('frontend.catalog.index') }}" role="search">
        <label for="fe-global-search-{{ $size }}" class="sr-only">Search products, services, suppliers</label>
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input
                type="search"
                id="fe-global-search-{{ $size }}"
                name="q"
                x-model="query"
                @focus="query.length >= 2 && (open = true)"
                autocomplete="off"
                placeholder="Search products, services, suppliers..."
                class="fe-focus-ring w-full {{ $size === 'hero' ? 'h-14 pl-12 pr-4 text-base rounded-2xl' : 'h-11 pl-11 pr-4 text-sm rounded-full' }} border bg-[--fe-surface-soft] placeholder:text-slate-400"
                style="border-color:var(--fe-border);"
            >
        </div>
    </form>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        @click.outside="open = false"
        class="absolute left-0 right-0 top-full mt-2 bg-white border rounded-xl shadow-lg z-50 max-h-96 overflow-y-auto"
        style="border-color:var(--fe-border);"
        role="listbox"
    >
        <template x-if="loading">
            <div class="px-4 py-6 text-center text-sm text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Searching...
            </div>
        </template>

        <template x-if="!loading && results && results.groups.length === 0">
            <div class="px-4 py-6 text-center text-sm text-slate-400">No results for "<span x-text="query"></span>"</div>
        </template>

        <template x-if="!loading && results">
            <div>
                <template x-for="group in results.groups" :key="group.label">
                    <div class="py-2 border-b last:border-b-0" style="border-color:var(--fe-border);">
                        <p class="px-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400" x-text="group.label"></p>
                        <template x-for="item in group.items" :key="item.url">
                            <a :href="item.url" class="flex items-center justify-between gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-[--fe-primary-soft] hover:text-[--fe-primary]">
                                <span class="truncate" x-text="item.title"></span>
                                <span class="text-[11px] text-slate-400 shrink-0" x-text="item.meta"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <a :href="results.view_all_url" class="block text-center text-sm font-semibold px-4 py-3" style="color:var(--fe-primary);">
                    View all results for "<span x-text="query"></span>"
                </a>
            </div>
        </template>
    </div>
</div>
