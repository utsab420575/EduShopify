<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['supplier', 'isSaved' => false]));

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

foreach (array_filter((['supplier', 'isSaved' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $location = collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ');
    $primaryType = $supplier->account?->supplierTypes?->first();
?>

<div class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col h-full">
    <div class="flex items-start gap-3 mb-3">
        <span class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 text-lg font-bold" style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
            <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

        </span>
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <a href="<?php echo e(route('frontend.suppliers.show', $supplier->slug)); ?>" class="fe-focus-ring text-sm font-semibold fe-line-clamp-2" style="color:var(--fe-text);"><?php echo e($supplier->display_name); ?></a>
            </div>
            <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => 'verified','class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified','class' => 'mt-1']); ?><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location): ?>
        <p class="text-xs mb-1 flex items-center gap-1.5" style="color:var(--fe-text-muted);">
            <i class="fa-solid fa-location-dot text-[10px]"></i> <?php echo e($location); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primaryType): ?>
        <p class="text-xs mb-3" style="color:var(--fe-text-muted);"><?php echo e($primaryType->name); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->description): ?>
        <p class="text-sm fe-line-clamp-2 mb-3" style="color:var(--fe-text-muted);"><?php echo e($supplier->description); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);"
         x-data="{
            isSaved: <?php echo e($isSaved ? 'true' : 'false'); ?>,
            loading: false,
            async saveSupplier() {
                <?php if(auth()->guard()->guest()): ?>
                    window.location.href = '<?php echo e(route('frontend.handoff.save-supplier', $supplier->slug)); ?>';
                    return;
                <?php endif; ?>

                if (this.loading) return;
                this.loading = true;

                try {
                    const res = await fetch('<?php echo e(route('buyer.saved-items.toggle')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                        },
                        body: JSON.stringify({
                            type: 'supplier',
                            id: <?php echo e($supplier->account_id); ?>,
                            action: 'save'
                        })
                    });

                    const data = await res.json();
                    if (res.ok) {
                        this.isSaved = true;
                        const toastDetail = {
                            message: data.message || 'Supplier is saved',
                            type: 'success',
                            actionUrl: '<?php echo e(route('buyer.saved-items.index', ['type' => 'supplier'])); ?>',
                            actionLabel: 'saved suppliers'
                        };
                        window.dispatchEvent(new CustomEvent('toast', { detail: toastDetail }));
                    } else {
                        const errDetail = {
                            message: data.message || 'Could not save supplier.',
                            type: 'danger'
                        };
                        window.dispatchEvent(new CustomEvent('toast', { detail: errDetail }));
                    }
                } catch (e) {
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: 'An error occurred while saving.',
                            type: 'danger'
                        }
                    }));
                } finally {
                    this.loading = false;
                }
            }
         }">
        <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $supplier->rating,'count' => $supplier->reviews_count ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->rating),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->reviews_count ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $attributes = $__attributesOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__attributesOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $component = $__componentOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__componentOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button"
                    @click="saveSupplier()"
                    :disabled="loading"
                    class="group fe-focus-ring text-xs font-semibold px-2.5 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-200 cursor-pointer shadow-xs focus:outline-none focus:ring-2 active:scale-[0.98]"
                    :class="isSaved
                        ? 'text-emerald-700 bg-emerald-50 border-emerald-300 hover:bg-emerald-100/70 hover:border-emerald-400 focus:ring-emerald-500/40'
                        : 'bg-white text-slate-700 border-slate-300 hover:bg-emerald-50/70 hover:text-emerald-700 hover:border-emerald-300 focus:ring-emerald-500/40'"
                    title="Save supplier to dashboard">
                <i :class="isSaved ? 'fa-solid fa-bookmark text-emerald-600' : 'fa-regular fa-bookmark text-slate-400 group-hover:text-emerald-600'"
                   class="text-xs transition-transform duration-200 group-hover:scale-110"
                   :class="loading ? 'animate-pulse' : ''"></i>
                <span x-text="isSaved ? 'Saved' : 'Save Supplier'"></span>
            </button>
            <a href="<?php echo e(route('frontend.suppliers.show', $supplier->slug)); ?>"
               class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 hover:text-slate-900 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400/30">
                View Supplier
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\supplier-card.blade.php ENDPATH**/ ?>