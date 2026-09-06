<?php
    $user = $user ?? auth()->user();
    $account = $account ?? $user?->activateTeamContext() ?? $user?->currentAccount;
?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('backend.layouts.partials.supplier._sidebar', ['account' => $account, 'user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar'); ?>
    <?php echo $__env->make('backend.layouts.partials.supplier._topbar', [
        'account'              => $account,
        'user'                 => $user,
        'unreadNotifications'  => $unreadNotifications ?? 0,
        'unreadMessages'       => $unreadMessages ?? 0,
        'topbarNotifications'  => $topbarNotifications ?? [],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('backend.layouts.partials.shared._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldContent('body'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\layouts\supplier.blade.php ENDPATH**/ ?>