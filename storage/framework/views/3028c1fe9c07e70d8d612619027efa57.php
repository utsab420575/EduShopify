<?php $__env->startSection('title', 'Edit Role in Permission: ' . ($role->display_name ?? $role->name)); ?>
<?php $__env->startSection('breadcrumb', 'Access Control / Edit Role in Permission'); ?>

<?php $__env->startSection('body'); ?>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Role in Permission: <?php echo e($role->display_name ?? $role->name); ?></h1>
                <p class="text-sm text-gray-500 mt-1">Configure default permissions mapped to this role.</p>
            </div>
            <a href="<?php echo e(route('admin.access-control.roles-in-permission.index')); ?>" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3.5 py-2 bg-white border border-gray-300 rounded-xl">
                &larr; Back to Overview
            </a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 space-y-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>• <?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.access-control.roles-in-permission.update', $role)); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Role Summary Box -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role Name</div>
                    <div class="text-lg font-bold text-gray-900"><?php echo e($role->display_name ?? $role->name); ?></div>
                    <div class="text-xs text-gray-400 font-mono"><?php echo e($role->name); ?> &bull; <?php echo e(ucfirst($role->capability_scope)); ?></div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                        <?php echo e($role->permissions->count()); ?> active permissions
                    </span>
                </div>
            </div>

            <!-- Permission Matrix Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-base font-bold text-gray-900">Permissions Matrix</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Toggle permissions below for this role.</p>
                </div>

                <?php if (isset($component)) { $__componentOriginal1699db5d4947caced6bb116ea947c89b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1699db5d4947caced6bb116ea947c89b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.permission-matrix','data' => ['groups' => $permissionGroups,'selected' => old('permissions', $rolePermissions),'roleScope' => $role->capability_scope]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.permission-matrix'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['groups' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($permissionGroups),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('permissions', $rolePermissions)),'role-scope' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($role->capability_scope)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1699db5d4947caced6bb116ea947c89b)): ?>
<?php $attributes = $__attributesOriginal1699db5d4947caced6bb116ea947c89b; ?>
<?php unset($__attributesOriginal1699db5d4947caced6bb116ea947c89b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1699db5d4947caced6bb116ea947c89b)): ?>
<?php $component = $__componentOriginal1699db5d4947caced6bb116ea947c89b; ?>
<?php unset($__componentOriginal1699db5d4947caced6bb116ea947c89b); ?>
<?php endif; ?>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="<?php echo e(route('admin.access-control.roles-in-permission.index')); ?>" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-7 py-2.5 text-sm font-semibold rounded-xl">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\access-control\roles-in-permission\edit.blade.php ENDPATH**/ ?>