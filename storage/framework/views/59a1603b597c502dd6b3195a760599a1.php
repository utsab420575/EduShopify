<?php
  $posts = $blogPosts ?? $events ?? collect();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($posts->isNotEmpty()): ?>
<section class="max-w-7xl mx-auto px-4 py-14" id="home-articles-section">
  <div class="text-center mb-4">
    <p class="text-emerald-600 text-xs font-semibold uppercase tracking-widest">Industry Insights &amp; Knowledge</p>
    <h2 class="text-3xl font-bold text-gray-900 mt-2">Latest <span class="text-emerald-500">Education &amp; Procurement</span> Articles</h2>
    <p class="text-gray-500 text-sm mt-2 max-w-lg mx-auto leading-relaxed">Expert guides, procurement strategies, and technology trends from verified suppliers and institutions.</p>
  </div>

  <div class="flex justify-end mb-5">
    <a href="<?php echo e(route('v2.blogs.index')); ?>" class="text-emerald-600 hover:text-emerald-700 text-sm font-semibold hover:underline inline-flex items-center gap-1 group">
      View all articles <span class="transform group-hover:translate-x-1 transition-transform">→</span>
    </a>
  </div>

  
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" data-skeleton-target="#events-grid">
    <div class="skel-block" style="height:340px; border-radius: 16px;"></div>
    <div class="skel-block" style="height:340px; border-radius: 16px;"></div>
    <div class="skel-block" style="height:340px; border-radius: 16px;"></div>
    <div class="skel-block" style="height:340px; border-radius: 16px;"></div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 cards-hidden" id="events-grid">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $isModel = $post instanceof \App\Models\BlogPost;
        $title = $isModel ? $post->title : ($post['title'] ?? '');
        $slug = $isModel ? $post->slug : '#';
        $cover = $isModel ? $post->coverImageUrl() : asset('images/herosection.png');
        $categoryName = $isModel
            ? ($post->category?->name ?? 'Article')
            : ($post['category'] ?? 'EdTech');
        $date = $isModel
            ? ($post->published_at?->format('M d, Y') ?? '')
            : ($post['date'] ?? '');
        $readTime = $isModel ? ($post->reading_time_minutes ?? 5) : 5;
        $excerpt = $isModel ? $post->excerpt : ($post['location'] ?? '');
        $isFeatured = $isModel ? (bool)$post->featured : false;
        $authorName = $isModel
            ? ($post->account?->display_name ?? $post->account?->supplierProfile?->display_name ?? $post->account?->buyerProfile?->display_name ?? 'EduShopify Member')
            : 'EduShopify';
        $postUrl = $isModel ? route('v2.blogs.show', $slug) : route('v2.blogs.index');
      ?>

      <a href="<?php echo e($postUrl); ?>"
         class="blog-card group block bg-white rounded-2xl border border-gray-200 hover:border-emerald-300 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col card-fade-up"
         style="animation-delay: <?php echo e($loop->index * 55); ?>ms">
        
        
        <div class="relative h-44 overflow-hidden bg-gray-100 shrink-0">
          <img
            src="<?php echo e($cover); ?>"
            alt="<?php echo e($title); ?>"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>

          
          <span class="absolute top-2.5 left-2.5 bg-emerald-600/90 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full shadow-sm">
            <?php echo e($categoryName); ?>

          </span>

          
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFeatured): ?>
            <span class="absolute top-2.5 right-2.5 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
              <span>★</span> Featured
            </span>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="p-4 flex-1 flex flex-col justify-between">
          <div>
            
            <div class="flex items-center gap-2 mb-2 text-[11px] text-gray-500">
              <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                <?php echo e(strtoupper(substr($authorName, 0, 1))); ?>

              </div>
              <span class="font-medium text-gray-700 truncate max-w-[110px]"><?php echo e($authorName); ?></span>
              <span class="text-gray-300">•</span>
              <span class="text-gray-400 whitespace-nowrap"><?php echo e($date); ?></span>
            </div>

            
            <h3 class="font-bold text-sm text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug mb-1.5">
              <?php echo e($title); ?>

            </h3>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($excerpt): ?>
              <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3">
                <?php echo e($excerpt); ?>

              </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-2 text-[11px] text-gray-400">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <?php echo e($readTime); ?>m read
              </span>

              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isModel && $post->likes_count > 0): ?>
                <span class="flex items-center gap-1 text-gray-400" title="Likes">
                  <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <?php echo e($post->likes_count); ?>

                </span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 group-hover:text-emerald-700 transition-colors">
              Read Article
              <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </span>
          </div>

        </div>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\home\partial\_events.blade.php ENDPATH**/ ?>