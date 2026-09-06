<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredSuppliers->isNotEmpty()): ?>
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <?php if (isset($component)) { $__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.section-heading','data' => ['eyebrow' => 'Suppliers','title' => 'Featured Suppliers','subtitle' => 'Institutions trust these suppliers for reliable sourcing.','action' => route('frontend.suppliers.index'),'actionLabel' => 'See all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => 'Suppliers','title' => 'Featured Suppliers','subtitle' => 'Institutions trust these suppliers for reliable sourcing.','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.suppliers.index')),'actionLabel' => 'See all']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a)): ?>
<?php $attributes = $__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a; ?>
<?php unset($__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a)): ?>
<?php $component = $__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a; ?>
<?php unset($__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a); ?>
<?php endif; ?>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $featuredSuppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $productCount = \App\Services\Catalog\PublicListingQuery::forSupplierAccount($supplier->account_id)->count();
                    ?>
                    <div class="relative rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow group"
                         x-data="{
                            isSaved: false,
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
                                        body: JSON.stringify({ type: 'supplier', id: <?php echo e($supplier->account_id); ?>, action: 'save' })
                                    });
                                    const data = await res.json();
                                    if (res.ok) {
                                        this.isSaved = true;
                                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Supplier is saved', type: 'success', actionUrl: '<?php echo e(route('buyer.saved-items.index', ['type' => 'supplier'])); ?>', actionLabel: 'saved suppliers' } }));
                                    } else {
                                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Could not save supplier.', type: 'danger' } }));
                                    }
                                } catch (e) {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'An error occurred while saving.', type: 'danger' } }));
                                } finally {
                                    this.loading = false;
                                }
                            }
                         }">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->banner): ?>
                            <img src="<?php echo e(Illuminate\Support\Facades\Storage::url($supplier->banner)); ?>" alt="<?php echo e($supplier->display_name); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" />
                        <?php else: ?>
                            <div class="w-full h-48 flex items-center justify-center bg-emerald-50 text-emerald-600 text-3xl font-bold group-hover:scale-105 transition-transform duration-300 font-display">
                                <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="absolute bottom-3 left-3">
                            <span class="badge-verified text-[10px] font-semibold px-1.5 py-0.5 rounded inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Verified</span>
                        </div>

                        <button type="button" @click="saveSupplier()" :disabled="loading"
                                class="absolute top-3 right-3 w-7 h-7 bg-white/90 rounded flex items-center justify-center hover:bg-white shadow-sm"
                                title="Save supplier to dashboard">
                            <i :class="isSaved ? 'fa-solid fa-heart text-emerald-600' : 'fa-regular fa-heart text-gray-500'" class="text-xs"></i>
                        </button>

                        <div class="p-3">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-7 h-7 rounded bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

                                </span>
                                <div class="min-w-0">
                                    <a href="<?php echo e(route('frontend.suppliers.show', $supplier->slug)); ?>" class="text-sm font-semibold text-gray-900 fe-line-clamp-2 hover:text-emerald-600"><?php echo e($supplier->display_name); ?></a>
                                    <p class="text-xs text-gray-500"><?php echo e($supplier->account?->supplierTypes?->first()?->name); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><span class="star">★</span> <?php echo e(number_format((float) $supplier->rating, 1)); ?> (<?php echo e($supplier->reviews_count ?? 0); ?>)</span>
                                <span><?php echo e($supplier->country?->name); ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1">
                                <span class="text-gray-400"><?php echo e($productCount); ?>+ Products</span>
                                <a href="<?php echo e(route('frontend.suppliers.show', $supplier->slug)); ?>" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\home\sections\_featured_suppliers.blade.php ENDPATH**/ ?>