
<?php
    $placeholderEvents = [
        ['title' => 'STEM & Robotics Expo 2026', 'category' => 'Expo', 'date' => 'Mar 12, 2026', 'location' => 'Dubai, UAE'],
        ['title' => 'EdTech Procurement Summit', 'category' => 'Summit', 'date' => 'Apr 8, 2026', 'location' => 'London, UK'],
        ['title' => 'Campus Furniture & AV Showcase', 'category' => 'Showcase', 'date' => 'May 20, 2026', 'location' => 'Singapore'],
        ['title' => 'Lab Equipment Buyers Meetup', 'category' => 'Meetup', 'date' => 'Jun 3, 2026', 'location' => 'Nairobi, Kenya'],
    ];
?>

<section class="py-12 lg:py-16 bg-white">
    <div class="fe-container">
        <?php if (isset($component)) { $__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.section-heading','data' => ['eyebrow' => 'Don\'t Miss Out','title' => 'Education STEM & Robotics Events']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => 'Don\'t Miss Out','title' => 'Education STEM & Robotics Events']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $placeholderEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="relative h-56 rounded-lg overflow-hidden group">
                    <img src="<?php echo e(asset('images/herosection.png')); ?>" alt="<?php echo e($event['title']); ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    <div class="absolute inset-0 bg-black/50"></div>
                    <span class="absolute top-3 left-3 bg-emerald-500 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded"><?php echo e($event['category']); ?></span>
                    <div class="absolute bottom-3 left-3 right-3">
                        <p class="text-white font-semibold text-sm fe-line-clamp-2"><?php echo e($event['title']); ?></p>
                        <p class="text-white/70 text-xs mt-1"><?php echo e($event['date']); ?> · <?php echo e($event['location']); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\home\sections\_events.blade.php ENDPATH**/ ?>