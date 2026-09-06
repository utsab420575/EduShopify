<?php $__env->startSection('title', $supplier->display_name . ' – Supplier Profile – Edushopify'); ?>

<?php $__env->startSection('content'); ?>
<div class="supplier-profile-page">
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

  
  <div class="hero-wrap">
    <div class="hero-banner">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->banner): ?>
        <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($supplier->banner)); ?>" alt="<?php echo e($supplier->display_name); ?>" />
      <?php else: ?>
        <div class="w-full h-full bg-emerald-50"></div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <div class="hero-save-btn">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </div>
      <div class="profile-bar">
        <div class="flex items-end gap-3">
          <div class="supplier-avatar"><?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?></div>
          <div>
            <h1 class="text-white font-bold text-2xl leading-tight mb-1.5"><?php echo e($supplier->display_name); ?></h1>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="badge-verified"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Verified Supplier</span>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demoBett): ?><span class="badge-bett">BETT Exhibitor</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demoFounding): ?><span class="badge-founding">Founding Supplier</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demoIse): ?><span class="badge-ise">ISE Exhibitor</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-[12px] text-white/85">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->country): ?>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><?php echo e($supplier->country->name); ?></span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->founded_year): ?>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Since <?php echo e($supplier->founded_year); ?></span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><?php echo e($productCount); ?> Products</span>
              <span class="flex items-center gap-1.5"><span class="star-filled">★</span><?php echo e(number_format((float) $supplier->rating, 1)); ?> <span class="text-white/70">(<?php echo e($supplier->reviews_count ?? 0); ?> Reviews)</span></span>
            </div>
          </div>
        </div>
        <div class="hidden sm:flex flex-col gap-2 shrink-0">
          <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-5 py-2.5 rounded-md flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Request Quotation
          </button>
          <button class="bg-white/90 hover:bg-white text-gray-800 text-sm font-medium px-5 py-2.5 rounded-md flex items-center gap-2 border border-white/40 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
          </button>
        </div>
      </div>
    </div>
    <div class="stats-bar">
      <div class="grid grid-cols-5 divide-x divide-gray-100">
        <div class="stat-col"><p class="val"><?php echo e($supplier->quotation_response_rate !== null ? round($supplier->quotation_response_rate).'%' : '—'); ?></p><p class="lbl">Response Rate</p></div>
        <div class="stat-col"><p class="val"><?php echo e($avgResponseHours !== null ? $avgResponseHours.' hrs' : '—'); ?></p><p class="lbl">Avg Response Time</p></div>
        <div class="stat-col"><p class="val"><?php echo e($rfqsCompleted); ?>+</p><p class="lbl">RFQs Completed</p></div>
        <div class="stat-col"><p class="val"><?php echo e($countriesServed); ?>+</p><p class="lbl">Countries Served</p></div>
        <div class="stat-col"><p class="val"><?php echo e($yearsInBusiness ?? '—'); ?><?php echo e($yearsInBusiness ? '+' : ''); ?></p><p class="lbl">Years in Business</p></div>
      </div>
    </div>
  </div>

  
  <div class="tab-bar mb-5">
    <button class="tab-btn active" onclick="switchTab(this,'about')">About Us</button>
    <button class="tab-btn" onclick="switchTab(this,'products')">Products</button>
    <button class="tab-btn" onclick="switchTab(this,'services')">Services</button>
    <button class="tab-btn" onclick="switchTab(this,'videos')">Videos</button>
    <button class="tab-btn" onclick="switchTab(this,'certifications')">Certifications</button>
    <button class="tab-btn" onclick="switchTab(this,'reviews')">Reviews</button>
    <button class="tab-btn" onclick="switchTab(this,'contact')">Contact</button>
  </div>

  <div class="flex flex-col lg:flex-row gap-5 items-start">

    
    <div class="w-full lg:flex-1 lg:max-w-[calc(100%-335px)] min-w-0 flex flex-col gap-4">

      
      <div id="tab-about" class="tab-panel flex flex-col gap-4" style="display:flex;flex-direction:column;gap:16px;">

        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-3">About Us</h2>
          <div class="flex gap-4">
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-600 leading-relaxed about-clamp">
                <span class="text-emerald-600 font-medium"><?php echo e($supplier->display_name); ?></span>
                <?php echo e($supplier->description ?? 'has not added a company description yet.'); ?>

              </p>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->description && strlen($supplier->description) > 180): ?>
                <button type="button" class="about-readmore-btn" onclick="toggleAboutReadMore(this)">Read more</button>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->videos->isNotEmpty()): ?>
              <?php ($firstVideo = $supplier->videos->first()); ?>
              <div class="shrink-0 w-36 h-24 relative rounded-lg overflow-hidden cursor-pointer group" onclick="switchTab(document.querySelectorAll('.tab-btn')[3],'videos')">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstVideo->thumbnailUrl()): ?>
                  <img src="<?php echo e($firstVideo->thumbnailUrl()); ?>" alt="<?php echo e($firstVideo->title); ?>" class="absolute inset-0 w-full h-full object-cover" />
                <?php else: ?>
                  <div class="absolute inset-0 bg-emerald-900"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="absolute inset-0 bg-black/30 flex items-end justify-center pb-2">
                  <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-9 h-9 bg-white/90 rounded-full flex items-center justify-center group-hover:bg-white transition-colors shadow">
                      <svg class="w-4 h-4 text-emerald-600 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                  </div>
                  <span class="text-white text-[10px] font-medium relative z-10 bg-black/40 px-1.5 py-0.5 rounded"><?php echo e($firstVideo->title ?? 'Company Video'); ?></span>
                </div>
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredProducts->isNotEmpty()): ?>
          <div class="sec-card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-[15px] font-bold text-gray-900">Featured Products</h2>
              <a href="#" onclick="switchTab(document.querySelectorAll('.tab-btn')[1],'products');return false;" class="text-sm text-emerald-600 font-medium hover:underline">View all products →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="fp-card">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->primaryImage): ?>
                    <img src="<?php echo e($product->primaryImage->getUrl()); ?>" class="w-full h-24 object-cover" />
                  <?php else: ?>
                    <div class="w-full h-24 bg-gray-100"></div>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <div class="p-2.5">
                    <p class="text-sm font-semibold text-gray-900 leading-snug mb-0.5"><?php echo e($product->name); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?><p class="text-[11px] text-emerald-600 font-medium mb-1.5"><?php echo e($product->brand->name); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="<?php echo e(route('v2.products.show', $product->slug)); ?>" class="text-[11px] text-emerald-600 font-medium hover:underline">View Details →</a>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($services->isNotEmpty()): ?>
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Our Services</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow">
                  <div class="svc-icon mb-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->icon): ?>
                      <?php echo $service->icon->render('text-emerald-600 text-lg flex items-center justify-center'); ?>

                    <?php else: ?>
                      <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </div>
                  <p class="text-sm font-semibold text-gray-900 mb-1"><?php echo e($service->title); ?></p>
                  <p class="text-[11px] text-gray-500 leading-snug"><?php echo e(Str::limit($service->description, 45)); ?></p>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($achievements->isNotEmpty()): ?>
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Certifications &amp; Partners</h2>
            <div class="flex flex-wrap gap-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $achievements->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="cert-pill"><?php echo e($achievement->name); ?></span>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      </div>

      
      <div id="tab-products" class="tab-panel">
        <div class="sec-card">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-[15px] font-bold text-gray-900">Products (<?php echo e($productCount); ?>+)</h2>
            <a href="<?php echo e(route('v2.home')); ?>" class="text-sm text-emerald-600 font-medium hover:underline">View in Marketplace →</a>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allProducts->isNotEmpty()): ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pt-card">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->primaryImage): ?>
                    <img src="<?php echo e($product->primaryImage->getUrl()); ?>" class="w-full h-40 object-cover" />
                  <?php else: ?>
                    <div class="w-full h-40 bg-gray-100"></div>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <div class="p-3.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?><p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wide mb-1"><?php echo e(strtoupper($product->brand->name)); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="text-[15px] font-bold text-gray-900 mb-2"><?php echo e($product->name); ?></p>
                    <div class="flex items-center gap-1 mb-1.5 text-sm">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><span class="<?php echo e($i <= round($product->product_rating) ? 'star-filled' : 'star-empty'); ?>">★</span><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                      <span class="text-xs text-gray-400">(<?php echo e($product->product_reviews_count); ?>)</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                      <span class="text-xs text-gray-400 italic">$<?php echo e(number_format((float) $product->base_price, 2)); ?></span>
                      <a href="<?php echo e(route('v2.products.show', $product->slug)); ?>" class="text-sm text-emerald-600 font-semibold hover:underline flex items-center gap-1">View <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></a>
                    </div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-gray-400">No published products yet.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      
      <div id="tab-services" class="tab-panel">
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Our Services</h2>
          <p class="text-sm text-gray-500 mb-5">We offer comprehensive support to ensure your institution gets the most from our solutions.</p>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($services->isNotEmpty()): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <div class="svc-icon shrink-0">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->icon): ?>
                        <?php echo $service->icon->render('text-emerald-600 text-lg flex items-center justify-center'); ?>

                      <?php else: ?>
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div><p class="text-sm font-bold text-gray-900 mb-1"><?php echo e($service->title); ?></p><p class="text-xs text-gray-500 leading-relaxed"><?php echo e($service->description); ?></p></div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-gray-400">No services published yet.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      
      <div id="tab-videos" class="tab-panel">
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Videos</h2>
          <p class="text-sm text-gray-500 mb-5">Watch our product demos, company overview, and classroom showcase videos.</p>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->videos->isNotEmpty()): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="vid-card">
                  <div class="relative" style="padding-bottom:56.25%; height:0; overflow:hidden;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($video->resolvedProvider(), ['youtube', 'vimeo']) && $video->embedUrl()): ?>
                      <iframe src="<?php echo e($video->embedUrl()); ?>" class="absolute top-0 left-0 w-full h-full" frameborder="0" allowfullscreen title="<?php echo e($video->title); ?>"></iframe>
                    <?php else: ?>
                      <video src="<?php echo e($video->video_url); ?>" poster="<?php echo e($video->thumbnailUrl()); ?>" class="absolute top-0 left-0 w-full h-full object-cover" controls></video>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </div>
                  <div class="p-3"><p class="text-sm font-semibold text-emerald-600"><?php echo e($video->title); ?></p></div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-gray-400">No videos published yet.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      
      <div id="tab-certifications" class="tab-panel" style="display:none;flex-direction:column;gap:16px;">
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Certifications &amp; Accreditations</h2>
          <p class="text-sm text-emerald-600 mb-5">Our products and operations are certified by leading global bodies in education and quality management.</p>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($certifications->isNotEmpty()): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="cert-card">
                  <div class="cert-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
                  <div>
                    <p class="text-sm font-bold text-gray-900 mb-0.5"><?php echo e($cert->certification_name); ?></p>
                    <p class="text-[11px] text-emerald-600 mb-1.5"><?php echo e($cert->certification_title); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cert->certification_date): ?> · Since <?php echo e($cert->certification_date->format('Y')); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                    <p class="text-xs text-gray-500 leading-relaxed"><?php echo e($cert->certification_description); ?></p>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-gray-400">No certifications published yet.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($achievements->isNotEmpty()): ?>
          <div class="sec-card">
            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Industry Partnerships</h2>
            <div class="flex flex-wrap gap-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="cert-pill"><?php echo e($achievement->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>

      
      <div id="tab-reviews" class="tab-panel">
        <div class="sec-card">

          
          <div class="flex flex-col sm:flex-row items-center gap-5 p-5 border border-gray-200 rounded-lg mb-6 bg-white">
            <div class="text-center shrink-0">
              <p class="text-5xl font-extrabold text-gray-900 leading-none"><?php echo e(number_format((float) $supplier->rating, 1)); ?></p>
              <div class="flex gap-0.5 justify-center mt-2 text-xl">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><span class="<?php echo e($i <= round($supplier->rating) ? 'star-filled' : 'star-empty'); ?>">★</span><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <p class="text-xs text-gray-400 mt-1"><?php echo e($supplier->reviews_count ?? 0); ?> Reviews</p>
            </div>
          </div>

          <h2 class="text-[15px] font-bold text-gray-900 mb-4">Customer Reviews</h2>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rev-card mb-3">
              <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2.5">
                  <div class="rev-av bg-emerald-100 text-emerald-700"><?php echo e(strtoupper(substr($review->buyerAccount?->display_name ?? '?', 0, 1))); ?></div>
                  <div>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($review->buyerAccount?->display_name ?? 'Anonymous Buyer'); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->buyerAccount?->buyerProfile?->organization_name): ?>
                      <p class="text-xs text-gray-400"><?php echo e($review->buyerAccount->buyerProfile->organization_name); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </div>
                </div>
                <span class="text-xs text-gray-400"><?php echo e($review->published_at?->format('F Y')); ?></span>
              </div>
              <div class="flex gap-0.5 mb-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><span class="<?php echo e($i <= $review->rating ? 'star-filled' : 'star-empty'); ?>">★</span><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed<?php echo e($review->reply ? ' mb-2' : ''); ?>"><?php echo e($review->comment); ?></p>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->reply): ?>
                <div class="bg-gray-50 rounded-md p-2.5 border-l-2 border-emerald-400">
                  <p class="text-xs text-emerald-600 font-semibold mb-0.5">Supplier Reply:</p>
                  <p class="text-xs text-gray-500"><?php echo e($review->reply->reply); ?></p>
                </div>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-400">No reviews yet for this supplier.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
      </div>

      
      <div id="tab-contact" class="tab-panel" style="display:none;flex-direction:column;gap:16px;">

        
        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-1">Send a Message</h2>
          <p class="text-sm text-emerald-600 mb-5">Fill in the form and our team will get back to you within 24 hours.</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Full Name</label>
              <input type="text" placeholder="Your name" class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Email Address</label>
              <input type="email" placeholder="you@school.edu" class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Organization</label>
              <input type="text" placeholder="Your institution" class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Subject</label>
              <input type="text" placeholder="What is this about?" class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
            </div>
          </div>
          <div class="mb-5">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Message</label>
            <textarea rows="4" placeholder="Tell us what you need..." class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 resize-none"></textarea>
          </div>
          <button type="button" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-3 rounded-md flex items-center justify-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Send Message
          </button>
        </div>

        <div class="sec-card">
          <h2 class="text-[15px] font-bold text-gray-900 mb-4">Contact Details</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->contact_phone): ?>
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.27 2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.08 6.08l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Phone</p><p class="text-sm text-gray-800 font-medium"><?php echo e($supplier->contact_phone); ?></p></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->contact_email): ?>
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Email</p><p class="text-sm text-gray-800 font-medium"><?php echo e($supplier->contact_email); ?></p></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->website): ?>
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Website</p><p class="text-sm text-gray-800 font-medium"><?php echo e($supplier->website); ?></p></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->city || $supplier->country): ?>
              <div class="contact-tile"><div class="contact-icon"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div><div><p class="text-[10px] text-gray-400 mb-0.5">Head Office</p><p class="text-sm text-gray-800 font-medium"><?php echo e(collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ')); ?></p></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->account?->socialLinks?->isNotEmpty()): ?>
            <div class="flex items-center gap-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->account->socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($link->url); ?>" target="_blank" rel="noopener" class="soc-icon" title="<?php echo e($link->label ?? 'Social link'); ?>">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

      </div>

    </div>

    
    <div class="w-full lg:w-[310px] xl:w-[330px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">

      <div class="side-card">
        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-md flex items-center justify-center gap-2 mb-2.5 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Request Quotation
        </button>
        <button class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contact Supplier
        </button>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->contact_email || $supplier->website): ?>
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-3">Contact Information</p>
          <div class="flex flex-col gap-2.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->contact_email): ?>
              <div class="flex items-center gap-2 text-sm text-gray-600"><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><?php echo e($supplier->contact_email); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->website): ?>
              <div class="flex items-center gap-2 text-sm text-emerald-600"><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg><a href="<?php echo e($supplier->website); ?>" target="_blank" rel="noopener" class="hover:underline"><?php echo e($supplier->website); ?></a></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->businessHours->isNotEmpty()): ?>
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-3">Business Hours</p>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplier->businessHours->sortBy('day_of_week'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="h-row">
              <span class="text-gray-700"><?php echo e(\Carbon\Carbon::create()->startOfWeek()->addDays($hour->day_of_week)->format('D')); ?></span>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hour->is_open): ?>
                <span class="text-gray-500"><?php echo e(\Carbon\Carbon::parse($hour->open_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($hour->close_time)->format('g:i A')); ?></span>
              <?php else: ?>
                <span class="closed">Closed</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->city || $supplier->country): ?>
        <div class="side-card">
          <p class="text-sm font-semibold text-gray-900 mb-2">Head Office</p>
          <p class="text-sm text-gray-600 mb-1"><?php echo e(collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ')); ?></p>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <div class="side-card">
        <p class="text-sm font-semibold text-gray-900 mb-3">Share Profile</p>
        <div class="flex items-center gap-2">
          <button class="share-btn" title="Facebook"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></button>
          <button class="share-btn" title="LinkedIn"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></button>
          <button class="share-btn" title="Copy link" onclick="navigator.clipboard && navigator.clipboard.writeText(window.location.href)"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></button>
        </div>
      </div>

    </div>

  </div>

  
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($similarSuppliers->isNotEmpty()): ?>
    <div class="mt-12 pt-8 border-t border-gray-100">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">You May Also Like</h2>
        <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium hover:underline">See all →</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $similarSuppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="product-card-fade-up" style="animation-delay: <?php echo e($loop->index * 55); ?>ms">
            <?php echo $__env->make('frontend_new.components.supplier-card', ['supplier' => $s], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; });
    var active = document.getElementById('tab-about');
    if (active) {
      active.style.display = 'flex';
      active.style.flexDirection = 'column';
      active.style.gap = '16px';
    }
  });

  function toggleAboutReadMore(btn) {
    var p = btn.previousElementSibling;
    if (!p) return;
    var expanded = p.classList.toggle('expanded');
    btn.textContent = expanded ? 'Read less' : 'Read more';
  }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend_new.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\suppliers\show.blade.php ENDPATH**/ ?>