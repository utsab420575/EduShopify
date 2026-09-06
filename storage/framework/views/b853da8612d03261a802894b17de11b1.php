
<a href="<?php echo e(route('v2.suppliers.show', $supplier->slug)); ?>" class="group block bg-white rounded-2xl border border-gray-200 hover:border-emerald-200 hover:shadow-md transition-all duration-200 overflow-hidden">

  <div class="relative h-32 bg-emerald-50">
    
    <div class="absolute inset-0 overflow-hidden">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->banner): ?>
        <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($supplier->banner)); ?>" alt="<?php echo e($supplier->display_name); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
      <?php else: ?>
        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100/50 text-emerald-600 font-bold text-2xl">
          <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php
      $supplierTypes = $supplier->account?->supplierTypes;
      $primaryType = $supplierTypes?->first()?->name;
      $totalTypes = $supplierTypes ? $supplierTypes->count() : 0;
      $extraTypesCount = max(0, $totalTypes - 1);
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primaryType): ?>
      <div class="absolute top-2.5 left-2.5 z-20 group/type">
        <span class="inline-flex items-center gap-1 bg-gray-900/85 hover:bg-gray-950 backdrop-blur-sm text-white text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 rounded shadow-sm cursor-help transition-all duration-150 border border-white/10" title="<?php echo e($supplierTypes->pluck('name')->implode(' · ')); ?>">
          <span><?php echo e(Str::limit($primaryType, 16)); ?></span>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraTypesCount > 0): ?>
            <span class="bg-emerald-500 text-white text-[9px] font-bold px-1 rounded-full leading-none">+<?php echo e($extraTypesCount); ?></span>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </span>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalTypes > 1): ?>
          <div class="absolute left-0 top-full mt-1.5 hidden group-hover/type:flex flex-col gap-1 z-30 bg-gray-950/95 backdrop-blur-md text-white text-[11px] rounded-lg p-2.5 shadow-xl whitespace-nowrap pointer-events-none border border-white/10 min-w-[130px]">
            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-800 pb-1 mb-0.5">Supplier Types</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplierTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span class="flex items-center gap-1.5 text-gray-200 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                <?php echo e($type->name); ?>

              </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="absolute top-2.5 right-2.5 z-10 w-7 h-7 bg-white/80 hover:bg-white backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors shadow-sm">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </button>
  </div>

  <div class="p-4">
    <div class="flex items-start gap-3 -mt-8 mb-2 relative">
      <div class="w-12 h-12 rounded-xl bg-white border-2 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0">
        <div class="w-full h-full bg-emerald-50 flex items-center justify-center">
          <span class="text-sm font-bold text-emerald-600"><?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?></span>
        </div>
      </div>
      <div class="pt-5 min-w-0 flex-1">
        <h3 class="font-bold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors truncate leading-tight"><?php echo e($supplier->display_name); ?></h3>
      </div>
    </div>

    <div class="flex flex-wrap gap-1 mb-2.5">
      <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded-full inline-flex items-center gap-1">✓ Verified</span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->demo_founding): ?>
        <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded-full">Founding</span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->demo_ise): ?>
        <span class="badge-ise text-[10px] font-medium px-1.5 py-0.5 rounded-full">ISE Exhibitor</span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->account?->supplierTypes?->isNotEmpty()): ?>
      <p class="text-[11px] text-gray-500 leading-relaxed mb-2.5 truncate"><?php echo e($supplier->account->supplierTypes->pluck('name')->implode(' · ')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex items-center justify-between text-[11px] text-gray-500 mb-3">
      <span class="flex items-center gap-1 text-amber-600 font-medium"><span class="star">★</span> <?php echo e(number_format((float) $supplier->rating, 1)); ?> <span class="text-gray-400 font-normal">(<?php echo e($supplier->reviews_count ?? 0); ?>)</span></span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->country): ?>
        <span class="flex items-center gap-1"><?php echo e($supplier->country->flag_emoji); ?> <?php echo e($supplier->country->name); ?></span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="flex items-center justify-between">
      <span class="text-[11px] text-gray-500">🛍 <?php echo e($supplier->product_count); ?>+ Products</span>
      <span class="text-[11px] text-emerald-600 font-semibold group-hover:underline">View Profile →</span>
    </div>
  </div>
</a>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/components/supplier-card.blade.php ENDPATH**/ ?>