
<ul class="space-y-2.5 border-t border-slate-100 pt-6 text-xs text-slate-600 font-medium">

    
    <li class="flex items-center gap-2">
        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_active_listings === 0): ?>
            Unlimited Listings
        <?php else: ?>
            Up to <?php echo e(number_format($plan->max_active_listings)); ?> Listing<?php echo e($plan->max_active_listings > 1 ? 's' : ''); ?>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </li>

    
    <li class="flex items-center gap-2">
        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_products === 0): ?>
            Unlimited Products
        <?php else: ?>
            Up to <?php echo e(number_format($plan->max_products)); ?> Product<?php echo e($plan->max_products > 1 ? 's' : ''); ?>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </li>

    
    <li class="flex items-center gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->rfq_delay_minutes === 0): ?>
            <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
            Instant RFQ Access
        <?php else: ?>
            <span class="text-slate-300 font-bold flex-shrink-0">✓</span>
            RFQ Access
            <span class="text-slate-400">(<?php echo e($plan->rfq_delay_minutes >= 60 ? floor($plan->rfq_delay_minutes/60).'hr' : $plan->rfq_delay_minutes.'min'); ?> delay)</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </li>

    
    <li class="flex items-center gap-2 <?php echo e($plan->has_rfq_notifications ? '' : 'opacity-40'); ?>">
        <span class="<?php echo e($plan->has_rfq_notifications ? 'text-emerald-500' : 'text-slate-300'); ?> font-bold flex-shrink-0">
            <?php echo e($plan->has_rfq_notifications ? '✓' : '✗'); ?>

        </span>
        RFQ Email Notifications
    </li>

    
    <li class="flex items-center gap-2 <?php echo e($plan->has_analytics ? '' : 'opacity-40'); ?>">
        <span class="<?php echo e($plan->has_analytics ? 'text-emerald-500' : 'text-slate-300'); ?> font-bold flex-shrink-0">
            <?php echo e($plan->has_analytics ? '✓' : '✗'); ?>

        </span>
        Analytics Dashboard
    </li>

    
    <li class="flex items-center gap-2 <?php echo e($plan->has_verified_badge ? '' : 'opacity-40'); ?>">
        <span class="<?php echo e($plan->has_verified_badge ? 'text-emerald-500' : 'text-slate-300'); ?> font-bold flex-shrink-0">
            <?php echo e($plan->has_verified_badge ? '✓' : '✗'); ?>

        </span>
        Verified Supplier Badge
    </li>

    
    <li class="flex items-center gap-2 <?php echo e($plan->has_homepage_placement ? '' : 'opacity-40'); ?>">
        <span class="<?php echo e($plan->has_homepage_placement ? 'text-emerald-500' : 'text-slate-300'); ?> font-bold flex-shrink-0">
            <?php echo e($plan->has_homepage_placement ? '✓' : '✗'); ?>

        </span>
        Featured Homepage Placement
    </li>

    
    <li class="flex items-center gap-2 <?php echo e($plan->has_team_members ? '' : 'opacity-40'); ?>">
        <span class="<?php echo e($plan->has_team_members ? 'text-emerald-500' : 'text-slate-300'); ?> font-bold flex-shrink-0">
            <?php echo e($plan->has_team_members ? '✓' : '✗'); ?>

        </span>
        Multiple Team Members
    </li>

</ul>
<?php /**PATH C:\laragon\www\edushopify\resources\views\supplier\_plan-features.blade.php ENDPATH**/ ?>