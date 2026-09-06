


<dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
        <div class="w-7 h-7 flex-shrink-0 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-xs">
            <i class="fa-solid fa-folder text-indigo-400 text-[11px]"></i>
        </div>
        <div class="min-w-0">
            <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Category</dt>
            <dd class="font-bold text-gray-900 truncate"><?php echo e($listing->mainCategory?->name ?? '—'); ?></dd>
        </div>
    </div>
    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
        <div class="w-7 h-7 flex-shrink-0 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-xs">
            <i class="fa-regular fa-registered text-purple-400 text-[11px]"></i>
        </div>
        <div class="min-w-0">
            <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Brand</dt>
            <dd class="font-bold text-gray-900 truncate"><?php echo e($listing->brand?->name ?? 'Unbranded / Generic'); ?></dd>
        </div>
    </div>
    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
        <div class="w-7 h-7 flex-shrink-0 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-xs">
            <i class="fa-solid fa-barcode text-gray-400 text-[11px]"></i>
        </div>
        <div class="min-w-0">
            <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">SKU / Model</dt>
            <dd class="font-bold font-mono text-gray-900 truncate"><?php echo e($listing->sku ?? '—'); ?></dd>
        </div>
    </div>
</dl>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->short_description): ?>
    <div class="mt-4 flex items-start gap-3 p-4 rounded-xl bg-indigo-50/70 border border-indigo-100">
        <i class="fa-solid fa-quote-left text-indigo-300 text-base mt-0.5 flex-shrink-0"></i>
        <p class="text-sm text-indigo-900 leading-relaxed font-medium"><?php echo e($listing->short_description); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->description): ?>
    <div class="mt-4">
        <div class="flex items-center gap-2 mb-2">
            <i class="fa-solid fa-align-left text-gray-400 text-xs"></i>
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Detailed Description</h4>
        </div>
        <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100 prose prose-xs max-w-none">
            <?php echo nl2br(e($listing->description)); ?>

        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\listings\partials\basic-info.blade.php ENDPATH**/ ?>