{{--
    Lightweight public-frontend toast — no such component existed before
    (confirmed: the public site only had full-page flash banners). Mounted
    once in master.blade.php; any component can trigger one via
    window.dispatchEvent(new CustomEvent('compare:toast', {detail:{message, type}})),
    exported as toast() from resources/js/frontend/comparison.js.
--}}
<div
    x-data="{
        toasts: [],
        push(message, type) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type: type || 'success' });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
    }"
    @compare:toast.window="push($event.detail.message, $event.detail.type)"
    class="fixed z-[200] bottom-4 right-4 left-4 sm:left-auto flex flex-col gap-2 items-end pointer-events-none"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto max-w-sm w-full sm:w-auto rounded-xl border px-4 py-3 text-sm font-medium shadow-lg flex items-center gap-2.5"
            :style="{
                success: 'background:var(--fe-success-soft);border-color:var(--fe-success);color:#166534;',
                warning: 'background:var(--fe-warning-soft);border-color:var(--fe-warning);color:#92400e;',
                danger:  'background:var(--fe-danger-soft);border-color:var(--fe-danger);color:#991b1b;',
                info:    'background:var(--fe-info-soft);border-color:var(--fe-info);color:#075985;',
            }[t.type]"
        >
            <i class="fa-solid" :class="{
                success: 'fa-circle-check',
                warning: 'fa-triangle-exclamation',
                danger:  'fa-circle-exclamation',
                info:    'fa-circle-info',
            }[t.type]"></i>
            <span x-text="t.message"></span>
        </div>
    </template>
</div>
