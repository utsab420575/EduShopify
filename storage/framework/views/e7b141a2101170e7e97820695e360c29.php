<?php
    $selectedSupplierTypeIdsOld = old('supplier_type_ids', $selectedSupplierTypeIds->toArray());
    $selectedCategoryIdsOld = old('category_ids', $selectedCategoryIds->toArray());
?>


<div id="sp-section-company" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: <?php echo e($spAccOpen('company', true)); ?> }" x-init="$watch('open', v => localStorage.setItem('sp-acc-company', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fa-solid fa-building text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Company Information</p>
                <p class="text-xs text-gray-400"><?php echo e($profile?->display_name ?: 'Name, type, categories, description'); ?></p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-transition class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="<?php echo e(route('supplier.company.profile.company.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php echo $__env->make('backend.supplier.company.partials._section-errors', ['section' => 'company'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Display / Trading Name <span class="text-red-500">*</span></label>
                        <input name="display_name" value="<?php echo e(old('display_name', $profile?->display_name)); ?>" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Legal Business Name</label>
                        <input name="legal_name" value="<?php echo e(old('legal_name', $profile?->legal_name)); ?>" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Type</label>
                        <input name="company_type" value="<?php echo e(old('company_type', $profile?->company_type)); ?>" type="text" placeholder="e.g. LLC, Pvt Ltd" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Founded Year</label>
                        <input name="founded_year" value="<?php echo e(old('founded_year', $profile?->founded_year)); ?>" type="number" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Employees</label>
                        <input name="employees" value="<?php echo e(old('employees', $profile?->employees)); ?>" type="number" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Type(s)</label>
                    <div class="flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplierTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="inline-flex items-center gap-1.5 text-xs font-medium border rounded-full px-3 py-1.5 cursor-pointer transition-colors <?php echo e(in_array($type->id, $selectedSupplierTypeIdsOld) ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'); ?>">
                                <input type="checkbox" name="supplier_type_ids[]" value="<?php echo e($type->id); ?>" <?php echo e(in_array($type->id, $selectedSupplierTypeIdsOld) ? 'checked' : ''); ?>>
                                <?php echo e($type->name); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categories You Supply</label>
                    <p class="text-xs text-gray-400 mb-2">Used to match you against Buyer RFQs open to eligible suppliers — separate from your published listings.</p>
                    <div class="max-h-56 overflow-y-auto pr-1 space-y-1 border border-gray-200 rounded-lg p-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <label class="flex items-center gap-2 text-xs py-1 px-1.5 rounded-lg hover:bg-gray-50 cursor-pointer" style="padding-left: <?php echo e(6 + $node['depth'] * 16); ?>px">
                                <input type="checkbox" name="category_ids[]" value="<?php echo e($node['id']); ?>" <?php echo e(in_array($node['id'], $selectedCategoryIdsOld) ? 'checked' : ''); ?>>
                                <span class="text-gray-800"><?php echo e($node['name']); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-xs text-gray-400 py-2">No categories available yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Overview / Description</label>
                    <textarea name="description" rows="3" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none transition resize-none" placeholder="Introduce your business to educational buyers..."><?php echo e(old('description', $profile?->description)); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Company Info
                </button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\company\partials\_company.blade.php ENDPATH**/ ?>