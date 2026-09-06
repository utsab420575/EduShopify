<div class="bg-emerald-500 py-5">
    <div class="fe-container grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-white">
        <div>
            <p class="text-2xl font-bold"><?php echo e(number_format($stats['suppliers'])); ?>+</p>
            <p class="text-sm opacity-80 mt-0.5">Suppliers</p>
        </div>
        <div>
            <p class="text-2xl font-bold"><?php echo e(number_format($stats['countries'])); ?>+</p>
            <p class="text-sm opacity-80 mt-0.5">Countries</p>
        </div>
        <div>
            <p class="text-2xl font-bold"><?php echo e(number_format($stats['products'])); ?>+</p>
            <p class="text-sm opacity-80 mt-0.5">Products</p>
        </div>
        <div>
            <p class="text-2xl font-bold"><?php echo e(number_format($stats['buyers'])); ?>+</p>
            <p class="text-sm opacity-80 mt-0.5">Verified Buyers</p>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\home\sections\_stats_bar.blade.php ENDPATH**/ ?>