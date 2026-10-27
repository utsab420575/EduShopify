{{-- Legal document body: $intro (string) + $sections ([heading => paragraph]) --}}
<section class="py-14 px-4">
  <div class="max-w-3xl mx-auto">
    <p class="text-sm text-gray-400 mb-8">Last updated: {{ $updated }}</p>

    <div class="grid gap-10 lg:grid-cols-[200px_1fr]">
      <nav class="hidden lg:block">
        <ul class="sticky top-24 space-y-2 text-sm">
          @foreach($sections as $heading => $body)
            <li><a href="#section-{{ $loop->iteration }}" class="text-gray-500 hover:text-emerald-600 transition-colors">{{ $loop->iteration }}. {{ $heading }}</a></li>
          @endforeach
        </ul>
      </nav>

      <div class="space-y-8 text-[15px] leading-relaxed text-gray-600">
        <p>{{ $intro }}</p>

        @foreach($sections as $heading => $body)
          <div id="section-{{ $loop->iteration }}" class="scroll-mt-24">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ $loop->iteration }}. {{ $heading }}</h2>
            <p>{{ $body }}</p>
          </div>
        @endforeach

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-sm">
          Questions about this document? <a href="{{ route('v2.pages.contact') }}" class="font-semibold text-emerald-600 hover:underline">Contact us</a>.
        </div>
      </div>
    </div>
  </div>
</section>
