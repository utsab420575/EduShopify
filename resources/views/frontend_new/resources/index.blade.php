@extends('frontend_new.layouts.app')

@section('title', 'Resources & Insights – Edushopify')

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

  {{--
    No Article/Blog model exists in this app — every item below is
    demo/config content (config/frontend_new_demo.php), not real posts.
  --}}

  <h1 class="text-3xl font-bold text-gray-900 mb-2">Resources &amp; Insights</h1>
  <p class="text-sm text-gray-500 mb-6">Guides, news, and case studies for education procurement.</p>

  <div class="flex items-center gap-2 flex-wrap mb-8">
    <button class="filter-tab-btn tag-active text-xs font-medium px-3 py-1.5 rounded-full" data-cat="all">All</button>
    <button class="filter-tab-btn tag-inactive text-xs font-medium px-3 py-1.5 rounded-full" data-cat="Guides">Guides</button>
    <button class="filter-tab-btn tag-inactive text-xs font-medium px-3 py-1.5 rounded-full" data-cat="News">News</button>
    <button class="filter-tab-btn tag-inactive text-xs font-medium px-3 py-1.5 rounded-full" data-cat="Case Studies">Case Studies</button>
  </div>

  <div class="flex flex-col lg:flex-row gap-8 items-start">
    <div class="flex-1 w-full min-w-0">

      @if($featured)
        <div class="article-card border border-gray-200 rounded-lg overflow-hidden mb-8 lg:flex" data-cat="{{ $featured['category'] }}">
          <div class="lg:w-1/2 h-48 lg:h-auto bg-emerald-50 flex items-center justify-center shrink-0">
            <img src="{{ asset('images/herosection.png') }}" alt="{{ $featured['title'] }}" class="w-full h-full object-cover" />
          </div>
          <div class="p-6">
            <span class="badge-posted text-xs font-semibold px-2.5 py-0.5 rounded">{{ $featured['category'] }}</span>
            <h3 class="text-xl font-bold text-gray-900 mt-3 mb-2">{{ $featured['title'] }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $featured['excerpt'] }}</p>
            <p class="text-xs text-gray-400 mb-3">{{ $featured['author'] }} · {{ \Illuminate\Support\Carbon::parse($featured['date'])->format('M j, Y') }}</p>
            <span class="text-sm text-emerald-600 font-medium">Read More →</span>
          </div>
        </div>
      @endif

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($articles as $article)
          <div class="article-card border border-gray-200 rounded-lg overflow-hidden" data-cat="{{ $article['category'] }}">
            <div class="h-44 bg-emerald-50">
              <img src="{{ asset('images/herosection.png') }}" alt="{{ $article['title'] }}" class="w-full h-full object-cover rounded-md" />
            </div>
            <div class="p-4">
              <span class="badge-posted text-xs font-semibold px-2.5 py-0.5 rounded">{{ $article['category'] }}</span>
              <h4 class="text-sm font-bold text-gray-900 mt-2 mb-1.5 leading-snug">{{ $article['title'] }}</h4>
              <p class="text-xs text-gray-500 leading-relaxed mb-3 line-clamp-2">{{ $article['excerpt'] }}</p>
              <div class="flex items-center justify-between">
                <p class="text-[11px] text-gray-400">{{ $article['author'] }} · {{ \Illuminate\Support\Carbon::parse($article['date'])->format('M j, Y') }}</p>
                <span class="text-xs text-emerald-600 font-medium">Read More →</span>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Sidebar --}}
    <aside class="w-full lg:w-72 shrink-0 flex flex-col gap-5 lg:sticky lg:top-20">
      <div class="detail-card p-5">
        <p class="text-sm font-semibold text-gray-900 mb-3">Popular Tags</p>
        <div class="flex flex-wrap gap-2">
          <span class="cert-pill">Procurement</span>
          <span class="cert-pill">RFQ</span>
          <span class="cert-pill">STEM</span>
          <span class="cert-pill">Verified Suppliers</span>
        </div>
      </div>
      @if($events->isNotEmpty())
        <div class="detail-card p-5">
          <p class="text-sm font-semibold text-gray-900 mb-3">Upcoming Events</p>
          <div class="flex flex-col gap-3">
            @foreach($events as $event)
              <div>
                <p class="text-sm font-medium text-gray-800">{{ $event['title'] }}</p>
                <p class="text-xs text-gray-400">{{ $event['date'] }} · {{ $event['location'] }}</p>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </aside>
  </div>
</main>

@push('scripts')
<script>
  document.querySelectorAll('.filter-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.filter-tab-btn').forEach(function (b) {
        b.classList.remove('tag-active');
        b.classList.add('tag-inactive');
      });
      this.classList.remove('tag-inactive');
      this.classList.add('tag-active');

      const cat = this.dataset.cat;
      document.querySelectorAll('.article-card').forEach(function (card) {
        card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
      });
    });
  });
</script>
@endpush
@endsection
