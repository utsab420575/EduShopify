<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['triggerId', 'action', 'context']));

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

foreach (array_filter((['triggerId', 'action', 'context']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ open: false }" @open-inquiry-<?php echo e($triggerId); ?>.window="open = true">
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" role="dialog" aria-modal="true" :aria-label="'Contact ' + '<?php echo e($context); ?>'" @keydown.escape.window="open = false">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40" @click="open = false"></div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--fe-border);">
                <div>
                    <p class="text-base font-semibold" style="color:var(--fe-text);">Contact <?php echo e($context); ?></p>
                    <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">This sends an inquiry — not an official RFQ.</p>
                </div>
                <button type="button" @click="open = false" class="fe-focus-ring w-9 h-9 rounded-lg flex items-center justify-center text-slate-500" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="<?php echo e($action); ?>" class="px-5 py-5 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="hidden" aria-hidden="true">
                    <label for="website-<?php echo e($triggerId); ?>">Leave blank</label>
                    <input type="text" id="website-<?php echo e($triggerId); ?>" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name-<?php echo e($triggerId); ?>" name="name" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                    </div>
                    <div>
                        <label for="email-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email-<?php echo e($triggerId); ?>" name="email" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                    </div>
                    <div>
                        <label for="phone-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Phone</label>
                        <input type="text" id="phone-<?php echo e($triggerId); ?>" name="phone" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                    </div>
                    <div>
                        <label for="organization-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Organization</label>
                        <input type="text" id="organization-<?php echo e($triggerId); ?>" name="organization" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                    </div>
                </div>
                <div>
                    <label for="subject-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Subject</label>
                    <input type="text" id="subject-<?php echo e($triggerId); ?>" name="subject" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                </div>
                <div>
                    <label for="message-<?php echo e($triggerId); ?>" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Message <span class="text-red-500">*</span></label>
                    <textarea id="message-<?php echo e($triggerId); ?>" name="message" required rows="4" class="fe-focus-ring w-full px-3 py-2.5 rounded-xl border text-sm" style="border-color:var(--fe-border);min-height:120px;"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Cancel</button>
                    <button type="submit" class="fe-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">Send Inquiry</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\inquiry-modal.blade.php ENDPATH**/ ?>