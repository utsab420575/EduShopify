<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 'default']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['size' => 'default']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="globalSearch" class="relative" @keydown.escape="closeOnEscape()">
    <form method="GET" action="<?php echo e(route('frontend.catalog.index')); ?>" role="search">
        <label for="fe-global-search-<?php echo e($size); ?>" class="sr-only">Search products, services, suppliers</label>
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input
                type="search"
                id="fe-global-search-<?php echo e($size); ?>"
                name="q"
                x-model="query"
                @focus="query.length >= 2 && (open = true)"
                autocomplete="off"
                placeholder="Search products, services, suppliers..."
                class="fe-focus-ring w-full <?php echo e($size === 'hero' ? 'h-14 pl-12 pr-4 text-base rounded-2xl' : 'h-11 pl-11 pr-4 text-sm rounded-full'); ?> border bg-[--fe-surface-soft] placeholder:text-slate-400"
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
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\search\global-search.blade.php ENDPATH**/ ?>