<form method="POST" action="<?php echo e(route('buyer.saved-items.toggle')); ?>" class="inline">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="type" value="<?php echo e($type); ?>">
    <input type="hidden" name="id" value="<?php echo e($item->id); ?>">
    <button type="submit" title="Remove from saved" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
</form>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\saved-items\partials\_remove-button.blade.php ENDPATH**/ ?>