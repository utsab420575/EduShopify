<?php $__env->startSection('title', 'Contact EduShopify'); ?>
<?php $__env->startSection('meta_description', 'Get in touch with the EduShopify team.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-10 sm:py-14">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Contact' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Contact' => null])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e)): ?>
<?php $attributes = $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e; ?>
<?php unset($__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e)): ?>
<?php $component = $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e; ?>
<?php unset($__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e); ?>
<?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 max-w-5xl mx-auto">
            <div class="lg:col-span-4">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-3" style="font-family:var(--font-display);color:var(--fe-text);">Get in touch</h1>
                <p class="text-sm mb-6" style="color:var(--fe-text-muted);">Have a question about procurement, suppliers or your account? Send us a message and our team will respond.</p>

                <div class="space-y-4 text-sm" style="color:var(--fe-text-muted);">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-question mt-0.5" style="color:var(--fe-primary);"></i>
                        <span>Check our <a href="<?php echo e(route('frontend.pages.faqs')); ?>" class="font-semibold" style="color:var(--fe-primary);">FAQs</a> for quick answers.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-clock mt-0.5" style="color:var(--fe-primary);"></i>
                        <span>We typically respond within 1–2 business days.</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="fe-card rounded-2xl p-6 sm:p-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                        <div class="rounded-xl border px-4 py-3 text-sm mb-5 flex items-center gap-2" style="background:var(--fe-success-soft);border-color:var(--fe-success);color:#166534;">
                            <i class="fa-solid fa-circle-check"></i> <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form method="POST" action="<?php echo e(route('frontend.pages.contact.submit')); ?>" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div class="hidden" aria-hidden="true">
                            <label for="fe-contact-website">Leave blank</label>
                            <input type="text" id="fe-contact-website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="fe-contact-name" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Name <span class="text-red-500">*</span></label>
                                <input type="text" id="fe-contact-name" name="name" value="<?php echo e(old('name')); ?>" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="border-color:var(--fe-border);">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label for="fe-contact-email" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="fe-contact-email" name="email" value="<?php echo e(old('email')); ?>" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="border-color:var(--fe-border);">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label for="fe-contact-phone" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Phone</label>
                                <input type="text" id="fe-contact-phone" name="phone" value="<?php echo e(old('phone')); ?>" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                            </div>
                            <div>
                                <label for="fe-contact-organization" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Organization</label>
                                <input type="text" id="fe-contact-organization" name="organization" value="<?php echo e(old('organization')); ?>" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                            </div>
                        </div>

                        <div>
                            <label for="fe-contact-subject" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Subject <span class="text-red-500">*</span></label>
                            <input type="text" id="fe-contact-subject" name="subject" value="<?php echo e(old('subject')); ?>" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="border-color:var(--fe-border);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label for="fe-contact-message" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Message <span class="text-red-500">*</span></label>
                            <textarea id="fe-contact-message" name="message" required rows="5" class="fe-focus-ring w-full px-3 py-2.5 rounded-xl border text-sm <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="border-color:var(--fe-border);min-height:130px;"><?php echo e(old('message')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <button type="submit" class="fe-btn-primary fe-focus-ring px-6 py-2.5 rounded-lg text-sm font-semibold">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\pages\contact.blade.php ENDPATH**/ ?>