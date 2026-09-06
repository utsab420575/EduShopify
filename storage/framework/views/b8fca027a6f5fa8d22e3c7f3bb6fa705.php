<?php $__env->startSection('title', $supplier->display_name.' — EduShopify'); ?>
<?php $__env->startSection('meta_description', Str::limit(strip_tags($supplier->description ?? ($supplier->display_name.' on EduShopify.')), 155)); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $location = collect([$supplier->city?->name, $supplier->state?->name, $supplier->country?->name])->filter()->implode(', ');
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    ?>

    <div class="h-36 sm:h-44" style="background:linear-gradient(120deg,var(--fe-primary),#2D8A67);"></div>

    <div class="fe-container">
        <div class="-mt-12 sm:-mt-14 flex items-end gap-4 pb-6">
            <span class="w-24 h-24 rounded-2xl border-4 border-white flex items-center justify-center text-3xl font-bold shrink-0 bg-white shadow-sm" style="color:var(--fe-primary);font-family:var(--font-display);">
                <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

            </span>
            <div class="pb-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);"><?php echo e($supplier->display_name); ?></h1>
                    <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => 'verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified']); ?><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm" style="color:var(--fe-text-muted);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location): ?><span><i class="fa-solid fa-location-dot text-xs mr-1"></i><?php echo e($location); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->account?->supplierTypes->isNotEmpty()): ?><span><?php echo e($supplier->account->supplierTypes->pluck('name')->implode(', ')); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $supplier->rating,'count' => $supplier->reviews_count ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->rating),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->reviews_count ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $attributes = $__attributesOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__attributesOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $component = $__componentOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__componentOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pb-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('buyer.rfqs.create', ['supplier_account_id' => $supplier->account_id])); ?>" class="fe-btn-primary fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quote</a>
            <?php else: ?>
                <a href="<?php echo e(route('frontend.handoff.request-quote-supplier', $supplier->slug)); ?>" class="fe-btn-primary fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quote</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button type="button" @click="$dispatch('open-inquiry-supplier')" class="fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Contact Supplier</button>
            <div x-data="{
                isSaved: <?php echo e($isSaved ? 'true' : 'false'); ?>,
                loading: false,
                async saveSupplier() {
                    <?php if(auth()->guard()->guest()): ?>
                        window.location.href = '<?php echo e(route('frontend.handoff.save-supplier', $supplier->slug)); ?>';
                        return;
                    <?php endif; ?>

                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const res = await fetch('<?php echo e(route('buyer.saved-items.toggle')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                            },
                            body: JSON.stringify({
                                type: 'supplier',
                                id: <?php echo e($supplier->account_id); ?>,
                                action: 'save'
                            })
                        });

                        const data = await res.json();
                        if (res.ok) {
                            this.isSaved = true;
                            const toastDetail = {
                                message: data.message || 'Supplier is saved',
                                type: 'success',
                                actionUrl: '<?php echo e(route('buyer.saved-items.index', ['type' => 'supplier'])); ?>',
                                actionLabel: 'saved suppliers'
                            };
                            window.dispatchEvent(new CustomEvent('toast', { detail: toastDetail }));
                        } else {
                            const errDetail = {
                                message: data.message || 'Could not save supplier.',
                                type: 'danger'
                            };
                            window.dispatchEvent(new CustomEvent('toast', { detail: errDetail }));
                        }
                    } catch (e) {
                        console.error(e);
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'An error occurred while saving.',
                                type: 'danger'
                            }
                        }));
                    } finally {
                        this.loading = false;
                    }
                }
            }"
            <?php if(request('save_intent')): ?>
                x-init="$nextTick(() => saveSupplier())"
            <?php endif; ?>>
                <button type="button"
                        @click="saveSupplier()"
                        :disabled="loading"
                        class="group fe-focus-ring px-4 py-2.5 rounded-xl text-sm font-semibold border flex items-center gap-2 transition-all duration-200 cursor-pointer shadow-xs focus:outline-none focus:ring-2 active:scale-[0.99]"
                        :class="isSaved
                            ? 'text-emerald-700 bg-emerald-50 border-emerald-300 hover:bg-emerald-100/70 hover:border-emerald-400 focus:ring-emerald-500/40 focus:border-emerald-500'
                            : 'bg-white text-slate-700 border-slate-300 hover:bg-emerald-50/70 hover:text-emerald-700 hover:border-emerald-300 focus:ring-emerald-500/40 focus:border-emerald-400'">
                    <i :class="isSaved ? 'fa-solid fa-bookmark text-emerald-600' : 'fa-regular fa-bookmark text-slate-400 group-hover:text-emerald-600'"
                       class="text-sm transition-transform duration-200 group-hover:scale-110"
                       :class="loading ? 'animate-pulse' : ''"></i>
                    <span x-text="isSaved ? 'Saved' : 'Save Supplier'"></span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-12">
            
            <aside class="lg:col-span-3 space-y-4">
                <div class="fe-card rounded-2xl p-5 lg:sticky lg:top-24">
                    <h3 class="text-sm font-semibold mb-3" style="color:var(--fe-text);">Company Overview</h3>
                    <dl class="space-y-2.5 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->founded_year): ?>
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Founded</dt><dd style="color:var(--fe-text);"><?php echo e($supplier->founded_year); ?></dd></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->employees): ?>
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Employees</dt><dd style="color:var(--fe-text);"><?php echo e($supplier->employees); ?></dd></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->quotation_response_rate): ?>
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Response Rate</dt><dd style="color:var(--fe-text);"><?php echo e(round($supplier->quotation_response_rate)); ?>%</dd></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->average_response_minutes): ?>
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Avg. Response</dt><dd style="color:var(--fe-text);"><?php echo e($supplier->average_response_minutes < 60 ? $supplier->average_response_minutes.' min' : round($supplier->average_response_minutes / 60).' hr'); ?></dd></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->website): ?>
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Website</dt><dd class="truncate max-w-[140px]"><a href="<?php echo e($supplier->website); ?>" target="_blank" rel="noopener nofollow" class="hover:underline" style="color:var(--fe-primary);"><?php echo e(parse_url($supplier->website, PHP_URL_HOST) ?? $supplier->website); ?></a></dd></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </dl>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->businessHours->isNotEmpty()): ?>
                        <h3 class="text-sm font-semibold mt-5 mb-2" style="color:var(--fe-text);">Business Hours</h3>
                        <ul class="space-y-1 text-xs" style="color:var(--fe-text-muted);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->businessHours->sortBy('day_of_week'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex justify-between">
                                    <span><?php echo e($days[$hour->day_of_week] ?? ''); ?></span>
                                    <span><?php echo e($hour->is_open ? ($hour->open_time.' – '.$hour->close_time) : 'Closed'); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->account?->exhibitions->isNotEmpty()): ?>
                        <h3 class="text-sm font-semibold mt-5 mb-2" style="color:var(--fe-text);">Exhibitions</h3>
                        <ul class="space-y-1 text-xs" style="color:var(--fe-text-muted);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->account->exhibitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exhibition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($exhibition->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exhibition->pivot->participation_year): ?>(<?php echo e($exhibition->pivot->participation_year); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </aside>

            
            <div class="lg:col-span-9" x-data="{ section: 'catalog' }">
                <div class="flex items-center gap-1 border-b mb-6 overflow-x-auto" style="border-color:var(--fe-border);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['catalog' => 'Catalog', 'about' => 'About', 'reviews' => 'Reviews', 'gallery' => 'Gallery']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button @click="section = '<?php echo e($key); ?>'" :class="section === '<?php echo e($key); ?>' ? 'font-semibold' : ''" :style="section === '<?php echo e($key); ?>' ? 'color:var(--fe-primary);border-color:var(--fe-primary)' : 'color:var(--fe-text-muted);border-color:transparent'" class="px-4 py-2.5 text-sm border-b-2 whitespace-nowrap">
                            <?php echo e($label); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div x-show="section === 'catalog'">
                    <div class="flex items-center gap-2 mb-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['all' => 'All', 'products' => 'Products ('.$productCount.')', 'services' => 'Services ('.$serviceCount.')']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('frontend.suppliers.show', [$supplier->slug, 'tab' => $key])); ?>" class="px-3.5 py-1.5 rounded-full text-xs font-medium <?php echo e($tab === $key ? 'text-white' : 'border'); ?>" <?php if($tab === $key): ?> style="background:var(--fe-primary);" <?php else: ?> style="border-color:var(--fe-border-strong);color:var(--fe-text-muted);" <?php endif; ?>>
                                <?php echo e($label); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listings->isEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-box-open','title' => 'No listings published yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-box-open','title' => 'No listings published yet']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $attributes = $__attributesOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__attributesOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $component = $__componentOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__componentOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if (isset($component)) { $__componentOriginal678b3726af759b898b6e4915a3a0d29a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal678b3726af759b898b6e4915a3a0d29a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.listing-card','data' => ['listing' => $listing]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.listing-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal678b3726af759b898b6e4915a3a0d29a)): ?>
<?php $attributes = $__attributesOriginal678b3726af759b898b6e4915a3a0d29a; ?>
<?php unset($__attributesOriginal678b3726af759b898b6e4915a3a0d29a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal678b3726af759b898b6e4915a3a0d29a)): ?>
<?php $component = $__componentOriginal678b3726af759b898b6e4915a3a0d29a; ?>
<?php unset($__componentOriginal678b3726af759b898b6e4915a3a0d29a); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if (isset($component)) { $__componentOriginalaa684956d2f805bd41f5b0a3a180039a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.pagination','data' => ['paginator' => $listings]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listings)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $attributes = $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $component = $__componentOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div x-show="section === 'about'" x-cloak>
                    <div class="fe-card rounded-2xl p-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->description): ?>
                            <p class="text-sm leading-relaxed whitespace-pre-line" style="color:var(--fe-text-muted);"><?php echo e($supplier->description); ?></p>
                        <?php else: ?>
                            <p class="text-sm" style="color:var(--fe-text-muted);">This supplier has not added a company description yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div x-show="section === 'reviews'" x-cloak>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reviews->isEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-star','title' => 'No published reviews yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-star','title' => 'No published reviews yet']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $attributes = $__attributesOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__attributesOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $component = $__componentOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__componentOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="fe-card rounded-2xl p-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $review->rating,'count' => 1,'size' => 'full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($review->rating),'count' => 1,'size' => 'full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $attributes = $__attributesOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__attributesOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $component = $__componentOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__componentOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
                                        <span class="text-xs" style="color:var(--fe-text-subtle);"><?php echo e($review->published_at?->format('M j, Y')); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->title): ?><p class="text-sm font-semibold mb-1" style="color:var(--fe-text);"><?php echo e($review->title); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <p class="text-sm" style="color:var(--fe-text-muted);"><?php echo e($review->comment); ?></p>
                                    <p class="text-xs mt-2" style="color:var(--fe-text-subtle);"><?php echo e($review->buyerAccount?->display_name ?? 'Verified Buyer'); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->reply): ?>
                                        <div class="mt-3 pl-4 border-l-2" style="border-color:var(--fe-border);">
                                            <p class="text-xs font-semibold mb-1" style="color:var(--fe-text);">Supplier response</p>
                                            <p class="text-sm" style="color:var(--fe-text-muted);"><?php echo e($review->reply->reply); ?></p>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if (isset($component)) { $__componentOriginalaa684956d2f805bd41f5b0a3a180039a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.pagination','data' => ['paginator' => $reviews]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reviews)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $attributes = $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $component = $__componentOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div x-show="section === 'gallery'" x-cloak>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->gallery->isEmpty() && $supplier->videos->isEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-images','title' => 'No gallery media yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-images','title' => 'No gallery media yet']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $attributes = $__attributesOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__attributesOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $component = $__componentOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__componentOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="aspect-square rounded-xl border flex items-center justify-center" style="border-color:var(--fe-border);background:var(--fe-surface-soft);">
                                    <i class="fa-solid fa-image text-2xl" style="color:var(--fe-text-subtle);"></i>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($video->video_url); ?>" target="_blank" rel="noopener" class="aspect-square rounded-xl border flex items-center justify-center relative" style="border-color:var(--fe-border);background:var(--fe-surface-soft);">
                                    <i class="fa-solid fa-circle-play text-3xl" style="color:var(--fe-text-subtle);"></i>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal2f2136dcb302dac670a7b401bfecb83a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f2136dcb302dac670a7b401bfecb83a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.inquiry-modal','data' => ['triggerId' => 'supplier','action' => route('frontend.inquiries.supplier', $supplier->slug),'context' => $supplier->display_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.inquiry-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['trigger-id' => 'supplier','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.inquiries.supplier', $supplier->slug)),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->display_name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f2136dcb302dac670a7b401bfecb83a)): ?>
<?php $attributes = $__attributesOriginal2f2136dcb302dac670a7b401bfecb83a; ?>
<?php unset($__attributesOriginal2f2136dcb302dac670a7b401bfecb83a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f2136dcb302dac670a7b401bfecb83a)): ?>
<?php $component = $__componentOriginal2f2136dcb302dac670a7b401bfecb83a; ?>
<?php unset($__componentOriginal2f2136dcb302dac670a7b401bfecb83a); ?>
<?php endif; ?>

    
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\suppliers\show.blade.php ENDPATH**/ ?>