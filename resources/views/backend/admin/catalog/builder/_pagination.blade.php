{{--
    Client-side pagination controls per design.md §18.1.
    Assumes the enclosing x-data exposes: page, totalPages, rangeStart, rangeEnd,
    filtered (array), goToPage(n). Same visual pattern as the server-side
    pagination in design.md §18/§9, just driven by Alpine state instead of a
    Laravel paginator, since this list's full dataset is already loaded
    client-side for instant search.
--}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-1 pt-4 mt-2 border-t border-gray-100" x-show="filtered.length > 0">
    <p class="text-xs text-gray-500">
        Showing <span class="font-medium text-gray-700" x-text="rangeStart"></span>
        to <span class="font-medium text-gray-700" x-text="rangeEnd"></span>
        of <span class="font-medium text-gray-700" x-text="filtered.length"></span> entries
    </p>
    <div class="flex items-center gap-1">
        <button type="button" @click="goToPage(page - 1)" :disabled="page === 1"
                class="text-xs font-medium px-3 py-1.5 rounded-lg border"
                :class="page === 1 ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            Previous
        </button>
        <template x-for="p in Array.from({ length: totalPages }, (_, i) => i + 1)" :key="p">
            <button type="button" @click="goToPage(p)"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                    :class="p === page ? 'text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'"
                    :style="p === page ? 'background:var(--theme-primary)' : ''">
                <span x-text="p"></span>
            </button>
        </template>
        <button type="button" @click="goToPage(page + 1)" :disabled="page === totalPages"
                class="text-xs font-medium px-3 py-1.5 rounded-lg border"
                :class="page === totalPages ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            Next
        </button>
    </div>
</div>
