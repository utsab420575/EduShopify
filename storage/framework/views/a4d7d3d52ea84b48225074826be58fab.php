
<div
    x-data="{
        toasts: [],
        init() {
            const handle = (detail) => {
                if (!detail || !detail.message) return;
                const now = Date.now();
                if (this.toasts.some(t => t.message === detail.message && (now - t.createdAt) < 600)) return;
                const id = now + Math.random();
                this.toasts.push({
                    id,
                    createdAt: now,
                    message: detail.message,
                    type: detail.type || 'success',
                    actionUrl: detail.actionUrl || null,
                    actionLabel: detail.actionLabel || null
                });
                setTimeout(() => { this.remove(id); }, 2000);
            };
            window.addEventListener('toast', (e) => handle(e.detail));
            window.addEventListener('compare:toast', (e) => handle(e.detail));
            window.marketplaceToast = (msg, type = 'success', url = null, label = null) => {
                handle({ message: msg, type, actionUrl: url, actionLabel: label });
            };
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    class="fixed z-[200] top-5 right-5 left-5 sm:left-auto flex flex-col gap-2.5 items-end pointer-events-none"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            class="pointer-events-auto w-auto min-w-[260px] max-w-sm rounded-2xl bg-white border border-slate-200/90 shadow-xl px-4 py-3.5 flex items-center justify-between gap-4 transition-all duration-200"
            style="animation: feToastIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;"
        >
            <div class="flex items-center gap-3 min-w-0">
                
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 border-2"
                     :class="{
                         'border-emerald-200 bg-emerald-50/90 text-emerald-500': t.type === 'success' || t.type === 'info',
                         'border-amber-200 bg-amber-50/90 text-amber-500': t.type === 'warning',
                         'border-rose-200 bg-rose-50/90 text-rose-500': t.type === 'danger' || t.type === 'error',
                     }">
                    <template x-if="t.type === 'success' || t.type === 'info'">
                        <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </template>
                    <template x-if="t.type === 'warning'">
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                    </template>
                    <template x-if="t.type === 'danger' || t.type === 'error'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </template>
                </div>

                
                <div class="text-xs sm:text-sm text-slate-700 font-medium leading-snug">
                    <div x-text="t.message"></div>
                    <template x-if="t.actionUrl">
                        <div class="mt-0.5">
                            <a :href="t.actionUrl"
                               class="font-bold underline text-slate-900 hover:text-emerald-600 inline-flex items-center gap-1 transition-colors">
                                <span x-text="t.actionLabel || 'compare'"></span>
                                <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            
            <button type="button" @click="remove(t.id)"
                    class="text-slate-300 hover:text-slate-500 transition-colors p-1 -mr-1 text-sm shrink-0 cursor-pointer"
                    aria-label="Close notification">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </template>
</div>

<style>
@keyframes feToastIn {
    0% { opacity: 0; transform: translateY(-12px) scale(0.95); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\common\toast.blade.php ENDPATH**/ ?>