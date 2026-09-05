@if($events->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-2">
    <p class="text-emerald-600 text-xs font-semibold uppercase tracking-widest">Don't Miss Out</p>
    <h2 class="text-3xl font-bold text-gray-900 mt-2">Education <span class="text-emerald-500">STEM &amp; Robotics</span> Events</h2>
    <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto leading-relaxed">Discover the world's top education and technology exhibitions — all in one place.</p>
  </div>

  <div class="flex justify-end mb-4">
    <a href="#" class="text-emerald-600 text-sm font-medium hover:underline">View all →</a>
  </div>

  {{-- Skeleton shown immediately; JS reveals #events-grid a short moment
       later — heading/"View all" above are real from the start. --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4" data-skeleton-target="#events-grid">
    <div class="skel-block" style="height:180px;"></div>
    <div class="skel-block" style="height:180px;"></div>
    <div class="skel-block" style="height:180px;"></div>
    <div class="skel-block" style="height:180px;"></div>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 cards-hidden" id="events-grid">
    @foreach($events as $event)
      <div class="event-card group cursor-pointer card-fade-up" style="animation-delay: {{ $loop->index * 55 }}ms">
        <div class="relative h-40 overflow-hidden rounded-lg">
          <img src="{{ asset('images/herosection.png') }}" alt="{{ $event['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
          <div class="absolute inset-0 bg-gradient-to-t {{ $event['overlay_class'] }}"></div>
          <span class="absolute top-2 left-2 event-badge {{ $event['badge_class'] }} text-white">{{ $event['category'] }}</span>
          <div class="absolute bottom-3 left-3 right-3">
            <p class="text-white font-bold text-sm leading-tight">{{ $event['title'] }}</p>
          </div>
        </div>
        <div class="pt-2 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">{{ $event['date'] }}</p>
            <p class="text-xs text-gray-400">📍 {{ $event['location'] }}</p>
          </div>
          <span class="text-gray-400 hover:text-gray-700">→</span>
        </div>
      </div>
    @endforeach
  </div>
</section>
@endif
