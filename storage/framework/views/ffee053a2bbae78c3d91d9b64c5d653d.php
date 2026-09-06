<?php $__env->startSection('title', 'Permissions for ' . $member->user->name); ?>
<?php $__env->startSection('breadcrumb', 'Organization / Members / ' . $member->user->name . ' / Permissions'); ?>

<?php $__env->startSection('body'); ?>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Custom Permissions: <?php echo e($member->user->name); ?></h1>
                <p class="text-sm text-gray-500 mt-1">Manage role-inherited permissions and direct permission overrides for this team member.</p>
            </div>
            <a href="<?php echo e(route('buyer.members.index')); ?>" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3.5 py-2 bg-white border border-gray-300 rounded-xl">
                &larr; Back to Members
            </a>
        </div>

        <!-- Role Summary Card -->
        <div class="bg-indigo-50/50 rounded-2xl border border-indigo-100 p-5 flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-700">Assigned Roles</div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $member->user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800">
                            <?php echo e($r->display_name ?: $r->name); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="text-xs text-gray-500">No roles assigned.</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">Inherited from Roles</div>
                <div class="text-sm font-bold text-gray-900"><?php echo e(count($inheritedPermissions)); ?> permissions</div>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('buyer.members.permissions.update', $member)); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Direct Permission Overrides</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Grant additional direct permissions specifically to this employee.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-gray-50 rounded-xl border border-gray-200/80 p-5">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                <?php echo e($groupName ?: 'General'); ?>

                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php ($isInherited = in_array($perm->name, $inheritedPermissions)); ?>
                                    <?php ($isDirect = in_array($perm->name, $directPermissions)); ?>
                                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border <?php echo e($isInherited ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-gray-200 hover:border-indigo-300'); ?> cursor-pointer transition-colors">
                                        <input type="checkbox" name="direct_permissions[]" value="<?php echo e($perm->name); ?>" <?php echo e($isDirect || $isInherited ? 'checked' : ''); ?> <?php echo e($isInherited ? 'disabled' : ''); ?> class="w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <div class="text-xs">
                                            <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                                <?php echo e($perm->display_name ?: $perm->name); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isInherited): ?>
                                                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Inherited</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono"><?php echo e($perm->name); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="<?php echo e(route('buyer.members.index')); ?>" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-semibold rounded-xl">
                    Save Direct Permissions
                </button>
            </div>
        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\organization\members\permissions.blade.php ENDPATH**/ ?>