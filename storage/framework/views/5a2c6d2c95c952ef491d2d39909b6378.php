
<?php
    $gallery     = $listing->getMedia('gallery');
    $primaryId   = $listing->primary_image_media_id;
    // Sort: primary first, then rest
    $heroFirst   = $gallery->sortByDesc(fn($m) => $m->id === $primaryId)->values();
    $firstMedia  = $heroFirst->first();
?>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6"
     x-data="{
         tab:     '<?php echo e(request('_tab', 'overview')); ?>',
         heroUrl: '<?php echo e($firstMedia?->getUrl()); ?>',
     }">

    
    <div class="xl:col-span-8 space-y-5">

        
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            
            <div class="relative bg-gray-50 flex items-center justify-center" style="min-height: 340px; max-height: 440px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstMedia): ?>
                    <img :src="heroUrl" alt="<?php echo e($listing->name); ?>"
                         class="w-full object-contain transition-all duration-300"
                         style="max-height: 440px;">
                    
                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full bg-amber-400/90 text-white shadow backdrop-blur-sm">
                        <i class="fa-solid fa-star text-[9px]"></i> Cover Photo
                    </span>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-20 text-gray-300">
                        <i class="fa-regular fa-image text-5xl mb-3"></i>
                        <p class="text-sm font-medium">No photos uploaded yet</p>
                        <a href="<?php echo e(route('supplier.catalog.listings.edit', $listing)); ?>"
                           class="mt-3 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Add photos in Edit Listing →
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($gallery->count() > 1): ?>
                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100 overflow-x-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $heroFirst; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                @click="heroUrl = '<?php echo e($media->getUrl()); ?>'"
                                class="flex-shrink-0 relative w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-150"
                                :class="heroUrl === '<?php echo e($media->getUrl()); ?>' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-400'">
                            <img src="<?php echo e($media->getUrl()); ?>" alt="" class="w-full h-full object-cover">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media->id === $primaryId): ?>
                                <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-amber-400 flex items-center justify-center shadow">
                                    <i class="fa-solid fa-star text-white text-[8px]"></i>
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="<?php echo e(route('supplier.catalog.listings.edit', $listing)); ?>"
                       class="flex-shrink-0 w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition text-center">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span class="text-[10px] font-medium mt-0.5">Add</span>
                    </a>
                </div>
            <?php elseif($gallery->isEmpty()): ?>
                
            <?php else: ?>
                
                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100">
                    <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                        <img src="<?php echo e($firstMedia->getUrl()); ?>" alt="" class="w-full h-full object-cover">
                    </div>
                    <a href="<?php echo e(route('supplier.catalog.listings.edit', $listing)); ?>"
                       class="flex-shrink-0 w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span class="text-[10px] font-medium mt-0.5">Add</span>
                    </a>
                    <p class="text-xs text-gray-400 ml-1">Add more product photos for better visibility.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            
            <nav class="flex gap-0 border-b border-gray-100 px-1 overflow-x-auto">
                <button type="button" @click="tab = 'overview'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                        :class="tab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-regular fa-file-lines mr-1.5 text-xs"></i>Overview
                </button>
                <button type="button" @click="tab = 'specifications'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                        :class="tab === 'specifications' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-sliders mr-1.5 text-xs"></i>Specifications
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty()): ?>
                        <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600"><?php echo e($groupedSpecifications->sum(fn($g) => count($g['items']))); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->isProduct()): ?>
                    <button type="button" @click="tab = 'variants'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'variants' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>Variants
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->variants->isNotEmpty()): ?>
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600"><?php echo e($listing->variants->count()); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>

            
            <div class="p-5">
                <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div x-show="tab === 'specifications'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-specifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->isProduct()): ?>
                    <div x-show="tab === 'variants'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-variants', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="xl:col-span-4 space-y-4">
        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\partials\listing-preview-tabbed.blade.php ENDPATH**/ ?>