{{--
    Section-completion bars for an accordion profile page (supplier company
    profile, buyer profile).

    The bars aren't listed here: one is built for every element on the page
    carrying data-profile-section="<title>" + data-filled="1|0" (plus an id),
    so a new section only needs those attributes on its root <div> to get a
    bar of its own. Clicking a bar sets that section's Alpine `open` flag and
    scrolls to it.

    wire:ignore keeps Livewire's re-render from rewriting the Alpine-built
    bars; the observer below re-reads data-filled instead, so a Livewire save
    that changes a section's state updates its bar without a page reload.
--}}
<div wire:ignore
     x-data="{
         sections: [],
         get filledCount() { return this.sections.filter(s => s.filled).length },
         init() {
             this.read();
             let queued = false;
             new MutationObserver(() => {
                 if (queued) return;
                 queued = true;
                 requestAnimationFrame(() => { queued = false; this.read(); });
             }).observe(document.body, {
                 subtree: true, childList: true,
                 attributes: true, attributeFilter: ['data-filled', 'data-profile-section'],
             });
         },
         read() {
             this.sections = [...document.querySelectorAll('[data-profile-section]')].map(el => ({
                 id: el.id,
                 title: el.dataset.profileSection,
                 filled: el.dataset.filled === '1',
             }));
         },
         go(section) {
             const el = document.getElementById(section.id);
             if (!el) return;
             const state = Alpine.$data(el);
             if (state && 'open' in state) state.open = true;
             this.$nextTick(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
         },
     }"
     x-cloak x-show="sections.length" {{ $attributes->merge(['class' => 'sm:text-right']) }}>
    <p class="text-xs font-semibold mb-1.5"
       :class="filledCount === sections.length ? 'text-green-700' : 'text-gray-600'">
        <span x-show="filledCount === sections.length"><i class="fa-solid fa-circle-check"></i> Profile complete</span>
        <span x-show="filledCount < sections.length">
            Profile <span x-text="filledCount"></span>/<span x-text="sections.length"></span> sections complete
        </span>
    </p>
    <div class="flex flex-wrap sm:justify-end gap-1">
        <template x-for="section in sections" :key="section.id">
            <button type="button" @click="go(section)"
                    :title="section.title + (section.filled ? ' — complete' : ' — incomplete, click to fill in')"
                    :aria-label="section.title + (section.filled ? ' (complete)' : ' (incomplete)')"
                    class="h-2 w-6 rounded-full transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-indigo-400"
                    :class="section.filled ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-200 hover:bg-amber-400'"></button>
        </template>
    </div>
</div>
