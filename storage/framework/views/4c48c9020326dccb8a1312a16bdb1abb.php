
<div class="mt-3 relative" x-data="{
    openPicker: false,
    search: '',
    selectedId: <?php echo e($selectedIconId ?? 'null'); ?>,
    get selected() { return iconCatalog.find(i => i.id == this.selectedId) || null; },
    get filtered() {
        if (!this.search.trim()) return iconCatalog;
        const q = this.search.toLowerCase();
        return iconCatalog.filter(i => i.name.toLowerCase().includes(q) || i.lib.toLowerCase().includes(q) || i.value.toLowerCase().includes(q));
    },
}">
    <label class="block text-xs font-medium text-gray-600 mb-1">Service Icon</label>
    <input type="hidden" name="icon_id" x-model="selectedId">
    <div class="flex items-center gap-2">
        <button type="button" @click="openPicker = !openPicker; if (openPicker) $nextTick(() => $refs.iconSearchInput?.focus())"
                class="flex-1 flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-violet-300 transition text-left cursor-pointer">
            <template x-if="selected">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-6 h-6 rounded bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 text-xs">
                        <template x-if="selected.type === 'fontawesome'"><i :class="selected.value"></i></template>
                        <template x-if="selected.type === 'svg'"><span class="w-3.5 h-3.5 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full fill-current" x-html="selected.value"></span></template>
                        <template x-if="selected.type === 'image_url'"><img :src="selected.value" class="w-3.5 h-3.5 object-contain"></template>
                    </span>
                    <span class="font-medium text-gray-800 truncate" x-text="selected.name"></span>
                    <span class="text-[10px] text-gray-400 font-normal truncate" x-text="'(' + selected.lib + ')'"></span>
                </div>
            </template>
            <template x-if="!selected">
                <span class="text-gray-400 text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-icons text-gray-300"></i>
                    <span>Select an icon (optional)...</span>
                </span>
            </template>
            <div class="flex items-center gap-1.5 shrink-0 text-gray-400">
                <span class="text-xs text-violet-600 font-medium" x-text="selected ? 'Change' : 'Browse'"></span>
                <i class="fa-solid fa-chevron-down text-[10px]" :class="openPicker && 'rotate-180'"></i>
            </div>
        </button>

        <template x-if="selected">
            <button type="button" @click="selectedId = null" title="Remove icon" class="w-9 h-9 rounded-lg border border-gray-200 text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center shrink-0 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </template>
    </div>

    
    <div x-show="openPicker" @click.outside="openPicker = false" x-transition x-cloak
         class="absolute z-[200] mt-1.5 left-0 w-full sm:w-[420px] bg-white rounded-xl shadow-2xl border border-gray-200 p-3">
        <div class="relative mb-2.5">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-ref="iconSearchInput" x-model="search" placeholder="Search icon by name or keyword..."
                   class="w-full text-xs rounded-lg border border-gray-200 pl-8 pr-7 py-2 focus:ring-2 focus:ring-violet-300 outline-none">
            <button type="button" x-show="search" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        <div class="grid grid-cols-6 sm:grid-cols-8 gap-1.5 max-h-56 overflow-y-auto p-1 border border-gray-100 rounded-lg bg-gray-50/50">
            <template x-for="ic in filtered" :key="ic.id">
                <button type="button" @click="selectedId = ic.id; openPicker = false"
                        :title="ic.name + ' (' + ic.lib + ')'"
                        class="w-10 h-10 rounded-lg flex flex-col items-center justify-center transition p-1 hover:scale-105 cursor-pointer text-base"
                        :class="selectedId == ic.id ? 'bg-violet-600 text-white ring-2 ring-violet-300 shadow-sm' : 'bg-white text-gray-700 hover:bg-violet-50 hover:text-violet-700 border border-gray-200/80'">
                    <template x-if="ic.type === 'fontawesome'"><i :class="ic.value"></i></template>
                    <template x-if="ic.type === 'svg'"><span class="w-4 h-4 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full fill-current" x-html="ic.value"></span></template>
                    <template x-if="ic.type === 'image_url'"><img :src="ic.value" class="w-4 h-4 object-contain"></template>
                </button>
            </template>
        </div>
        <template x-if="filtered.length === 0">
            <div class="py-4 text-center text-xs text-gray-400">
                No icons matching "<span x-text="search"></span>"
            </div>
        </template>
        <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
            <span x-text="filtered.length + ' of ' + iconCatalog.length + ' icons'"></span>
            <div class="flex items-center gap-2">
                <template x-if="selectedId">
                    <button type="button" @click="selectedId = null; openPicker = false" class="text-xs text-red-500 hover:underline cursor-pointer">Clear Icon</button>
                </template>
                <button type="button" @click="openPicker = false" class="text-xs text-violet-600 font-medium hover:underline cursor-pointer">Close</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\company\partials\_icon-picker.blade.php ENDPATH**/ ?>