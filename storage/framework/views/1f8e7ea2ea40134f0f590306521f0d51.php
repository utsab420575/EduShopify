<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="bg-white">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="max-w-4xl mx-auto px-4 pt-6">
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <?php echo e(session('error')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
            <div class="max-w-4xl mx-auto px-4 pt-6">
                <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <?php echo e(session('info')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="py-16 lg:py-20 border-b border-slate-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">Subscription Plans</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Choose the <span class="text-emerald-500">Right Plan</span> for Your Business
                </h1>
                <p class="text-slate-500 text-sm max-w-xl mx-auto">
                    Select a plan to access buyer RFQs, showcase your products, and grow your reach.
                </p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSubPlan): ?>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-full text-emerald-700 text-xs font-semibold mt-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        Active Plan: <?php echo e($activeSubPlan->name); ?>

                        &nbsp;·&nbsp;
                        Expires: <?php echo e(auth()->user()?->account?->activeSubscription?->expires_at?->format('d M Y') ?? 'Never'); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <?php
            // Merge Free plans + Monthly/Yearly plans, sorting by sort_order
            $monthlyViewPlans = $plans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'monthly')->sortBy('sort_order')->values();
            $yearlyViewPlans  = $plans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'yearly')->sortBy('sort_order')->values();

            $hasToggle       = $monthlyPlans->isNotEmpty() && $yearlyPlans->isNotEmpty();
        ?>
        <div
            x-data="{ billing: '<?php echo e($monthlyPlans->isNotEmpty() ? 'monthly' : 'yearly'); ?>' }"
            class="py-16 lg:py-20"
        >
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasToggle): ?>
            <div class="flex justify-center mb-12">
                <div class="inline-flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                    <button
                        @click="billing = 'monthly'"
                        :class="billing === 'monthly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    >Monthly</button>

                    <button
                        @click="billing = 'yearly'"
                        :class="billing === 'yearly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2"
                    >
                        Yearly
                        <?php
                            // Calculate average savings across all monthly/yearly plan pairs
                            $avgSaving = 0;
                            $pairCount = 0;
                            foreach ($yearlyPlans as $yp) {
                                $mp = $monthlyPlans->first();
                                if ($mp && $mp->price > 0 && $yp->price > 0) {
                                    $monthlyEquiv = $mp->price * 12;
                                    $saving = round((($monthlyEquiv - $yp->price) / $monthlyEquiv) * 100);
                                    if ($saving > 0) { $avgSaving += $saving; $pairCount++; }
                                }
                            }
                            $savingLabel = $pairCount > 0 ? round($avgSaving / $pairCount) : 0;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savingLabel > 0): ?>
                            <span class="bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">Save <?php echo e($savingLabel); ?>%</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monthlyViewPlans->isNotEmpty()): ?>
                <div x-show="billing === 'monthly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthlyViewPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="relative bg-white rounded-2xl p-8 border <?php echo e($plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300'); ?> w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->is_featured): ?>
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">
                                Best Value
                            </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?>
                            <div class="absolute <?php echo e($plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2'); ?>">
                                <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">
                                    +<?php echo e($plan->bonus_days); ?> Bonus Days!
                                </span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="space-y-6">
                                <div>
                                    <span class="inline-flex px-2.5 py-0.5 <?php echo e($plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'); ?> text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                        <?php echo e(ucfirst($plan->billing_type)); ?>

                                    </span>
                                    <h2 class="text-xl font-bold text-slate-900 font-display"><?php echo e($plan->name); ?></h2>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->isFree()): ?>
                                        <div class="mt-3 flex items-baseline">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-500">
                                            <?php echo e($plan->totalFreeDays()); ?> days free
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?><span class="text-amber-600 font-semibold"> (inc. +<?php echo e($plan->bonus_days); ?>d bonus)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <div class="mt-3 flex items-baseline gap-1">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display"><?php echo e($plan->formattedPrice()); ?></span>
                                            <span class="text-slate-500 text-sm">/mo</span>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?>
                                            <p class="mt-1 text-xs text-amber-600 font-semibold">+ <?php echo e($plan->bonus_days); ?> bonus days</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php echo $__env->make('supplier._plan-features', ['plan' => $plan], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>

                            <div class="mt-8">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSubPlan?->id === $plan->id): ?>
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                        ✓ Current Plan
                                    </div>
                                <?php elseif($plan->isFree() && !$isEligibleFree): ?>
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                        Not Eligible
                                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">Already claimed or had a premium plan</span>
                                    </div>
                                <?php else: ?>
                                    <form action="<?php echo e(route('supplier.subscribe', $plan->slug)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-full text-center px-4 py-3 rounded-xl <?php echo e($plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'); ?> text-xs font-semibold transition-colors">
                                            <?php echo e($plan->isFree() ? 'Start Free Trial' : 'Choose ' . $plan->name); ?>

                                        </button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yearlyViewPlans->isNotEmpty()): ?>
                <div x-show="billing === 'yearly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $yearlyViewPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $matchingMonthly = $monthlyPlans->first();
                            $yearlySaving = null;
                            if ($matchingMonthly && $matchingMonthly->price > 0 && $plan->price > 0) {
                                $monthlyEquiv = $matchingMonthly->price * 12;
                                $savingAmount = $monthlyEquiv - $plan->price;
                                if ($savingAmount > 0) {
                                    $yearlySaving = [
                                        'amount'  => $matchingMonthly->effectiveCurrencySymbol() . number_format($savingAmount, 0),
                                        'percent' => round(($savingAmount / $monthlyEquiv) * 100),
                                    ];
                                }
                            }
                        ?>
                        <div class="relative bg-white rounded-2xl p-8 border <?php echo e($plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300'); ?> w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->is_featured): ?>
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">
                                Best Value
                            </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yearlySaving): ?>
                            <div class="absolute top-4 right-4">
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full">
                                    Save <?php echo e($yearlySaving['amount']); ?>

                                </span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?>
                            <div class="absolute <?php echo e($plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2'); ?>">
                                <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">
                                    +<?php echo e($plan->bonus_days); ?> Bonus Days!
                                </span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="space-y-6">
                                <div>
                                    <span class="inline-flex px-2.5 py-0.5 <?php echo e($plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700'); ?> text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                        <?php echo e(ucfirst($plan->billing_type)); ?>

                                    </span>
                                    <h2 class="text-xl font-bold text-slate-900 font-display"><?php echo e($plan->name); ?></h2>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->isFree()): ?>
                                        <div class="mt-3 flex items-baseline">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-500">
                                            <?php echo e($plan->totalFreeDays()); ?> days free
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?><span class="text-amber-600 font-semibold"> (inc. +<?php echo e($plan->bonus_days); ?>d bonus)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <div class="mt-3 flex items-baseline gap-1">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display"><?php echo e($plan->formattedPrice()); ?></span>
                                            <span class="text-slate-500 text-sm">/yr</span>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yearlySaving): ?>
                                        <p class="mt-1 text-xs text-emerald-600 font-semibold">
                                            Save <?php echo e($yearlySaving['amount']); ?> vs monthly (<?php echo e($yearlySaving['percent']); ?>% off)
                                        </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->bonus_days > 0): ?>
                                            <p class="mt-1 text-xs text-amber-600 font-semibold">+ <?php echo e($plan->bonus_days); ?> bonus days</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php echo $__env->make('supplier._plan-features', ['plan' => $plan], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>

                            <div class="mt-8">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSubPlan?->id === $plan->id): ?>
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                        ✓ Current Plan
                                    </div>
                                <?php elseif($plan->isFree() && !$isEligibleFree): ?>
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                        Not Eligible
                                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">Already claimed or had a premium plan</span>
                                    </div>
                                <?php else: ?>
                                    <form action="<?php echo e(route('supplier.subscribe', $plan->slug)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-full text-center px-4 py-3 rounded-xl <?php echo e($plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'); ?> text-xs font-semibold transition-colors">
                                            <?php echo e($plan->isFree() ? 'Start Free Trial' : 'Choose ' . $plan->name); ?>

                                        </button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plans->isEmpty()): ?>
                <div class="text-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">No plans available right now. Please check back soon.</p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>

            
            <div class="max-w-3xl mx-auto px-4 mt-16 text-center">
                <p class="text-slate-500 text-sm">
                    Have questions? <a href="mailto:support@edushopify.com" class="text-emerald-600 font-semibold hover:underline">Contact our team</a> — we're happy to help you choose the right plan.
                </p>
            </div>
        </div>
    </div>

     <?php $__env->slot('scripts', null, []); ?> 
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\supplier\pricing.blade.php ENDPATH**/ ?>