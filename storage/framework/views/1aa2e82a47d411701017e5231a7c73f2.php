
<div class="space-y-4">
    <div>
        <p class="text-xs text-gray-500 mb-1">Attribute</p>
        <p class="text-sm font-bold text-gray-900"><?php echo e($attributeName); ?></p>
    </div>

    <div>
        <p class="text-xs text-gray-500 mb-2">Existing values for this attribute</p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existing->isEmpty()): ?>
            <p class="text-xs text-gray-400">No existing values yet — this would be the first.</p>
        <?php else: ?>
            <div class="flex flex-wrap gap-1.5" x-data="{ pending: <?php echo e(Js::from(strtolower($customValue))); ?> }">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border"
                          x-data="{ existingVal: <?php echo e(Js::from(strtolower($val))); ?> }"
                          :class="existingVal.includes(pending) || pending.includes(existingVal) ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                          :title="existingVal.includes(pending) || pending.includes(existingVal) ? 'Looks similar to the value being promoted' : ''">
                        <?php echo e($val); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Amber chips look similar to the value below — check they're not the same thing spelled differently.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="p-3 rounded-lg border border-indigo-200 bg-indigo-50/60">
        <p class="text-[10px] font-semibold text-indigo-500 uppercase mb-1">About to promote</p>
        <p class="text-sm font-bold text-indigo-900"><?php echo e($customValue); ?></p>
        <p class="text-xs text-indigo-600 mt-0.5">Used by <?php echo e($usageCount); ?> <?php echo e(Str::plural('listing', $usageCount)); ?></p>
    </div>
</div>

<form method="POST" action="<?php echo e(route('admin.catalog.custom-attribute-values.approve')); ?>" class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-gray-100">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="attribute_id" value="<?php echo e($attributeId); ?>">
    <input type="hidden" name="custom_value" value="<?php echo e($customValue); ?>">
    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Confirm Promote</button>
</form>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\approvals\partials\custom-value-duplicate-check.blade.php ENDPATH**/ ?>