
<?php ($gallery = $listing->getMedia('gallery')); ?>
<?php ($primaryId = $listing->primary_image_media_id); ?>

<?php ($sortedGallery = $gallery->sortByDesc(fn($m) => $m->id === $primaryId)->values()); ?>
<?php ($firstMedia = $sortedGallery->first()); ?>

<div x-data="{ heroUrl: '<?php echo e($firstMedia?->getUrl()); ?>' }">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstMedia): ?>
        <div class="relative rounded-xl overflow-hidden bg-gray-50 border border-gray-200 mb-3 flex items-center justify-center"
             style="min-height: 260px; max-height: 360px;">
            <img :src="heroUrl" alt="<?php echo e($listing->name); ?>"
                 class="w-full object-contain transition-all duration-300"
                 style="max-height: 360px;">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full bg-indigo-600/90 text-white shadow backdrop-blur-sm">
                <i class="fa-solid fa-star text-indigo-200 text-[9px]"></i> Primary
            </span>
            <div class="absolute top-3 right-3 text-xs font-medium text-gray-600 bg-white/80 backdrop-blur-sm px-2 py-1 rounded-lg border border-gray-200 shadow-xs">
                <?php echo e($gallery->count()); ?> photo<?php echo e($gallery->count() !== 1 ? 's' : ''); ?>

            </div>
        </div>

        
        <div class="flex items-center gap-2 flex-nowrap overflow-x-auto pb-1 mb-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sortedGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button"
                        @click="heroUrl = '<?php echo e($media->getUrl()); ?>'"
                        class="flex-shrink-0 relative w-14 h-14 rounded-lg overflow-hidden border-2 transition-all duration-150"
                        :class="heroUrl === '<?php echo e($media->getUrl()); ?>' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-400'">
                    <img src="<?php echo e($media->getUrl()); ?>" alt="" class="w-full h-full object-cover">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media->id === $primaryId): ?>
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center shadow">
                            <i class="fa-solid fa-star text-white text-[7px]"></i>
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sortedGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#listing-photo-<?php echo e($media->id); ?>"
                   class="flex-shrink-0 relative w-14 h-14 rounded-lg overflow-hidden border-2 border-transparent"
                   style="display:none">
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <p class="text-[11px] text-gray-400 mb-3 flex items-center gap-1">
            <i class="fa-solid fa-magnifying-glass-plus text-gray-300"></i>
            Click a thumbnail to preview. Click the hero image below to zoom.
        </p>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sortedGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="#listing-photo-<?php echo e($media->id); ?>" class="block">
                <div class="relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 mb-2 flex items-center justify-center cursor-zoom-in hover:opacity-90 transition" style="height: 60px; display:none">
                </div>
            </a>
            <div id="listing-photo-<?php echo e($media->id); ?>" class="listing-lightbox fixed inset-0 z-50 items-center justify-center p-4">
                <a href="#" class="absolute inset-0 bg-gray-900/75" aria-label="Close"></a>
                <div class="relative max-w-4xl w-full">
                    <a href="#" class="absolute -top-9 right-0 text-white/80 hover:text-white text-sm" aria-label="Close">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </a>
                    <img src="<?php echo e($media->getUrl()); ?>" alt="<?php echo e($listing->name); ?>" class="w-full max-h-[82vh] object-contain rounded-xl bg-white shadow-2xl">
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php else: ?>
        <div class="py-12 text-center text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <i class="fa-solid fa-image text-4xl mb-2"></i>
            <p class="text-sm font-medium">No media uploaded for this listing.</p>
            <p class="text-xs text-gray-300 mt-1">The supplier has not uploaded any product photos yet.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\listings\partials\gallery.blade.php ENDPATH**/ ?>