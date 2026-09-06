<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('backend.layouts.partials.admin._sidebar', ['user' => $user ?? auth()->user(), 'approvalQueueTotal' => $approvalQueueTotal ?? 0, 'approvalQueues' => $approvalQueues ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar'); ?>
    <?php echo $__env->make('backend.layouts.partials.admin._topbar', [
        'user' => $user ?? auth()->user(),
        'unreadNotifications' => $unreadNotifications ?? 0,
        'topbarNotifications' => $topbarNotifications ?? [],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('backend.layouts.partials.shared._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldContent('body'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views/backend/layouts/admin.blade.php ENDPATH**/ ?>