@extends('frontend_new.layouts.app')

@section('title', 'Careers | Edushopify')

@section('content')

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Careers',
    'title' => 'Help Us Connect Education',
    'subtitle' => "We're building the marketplace that helps institutions source better resources. Join us if that sounds like work worth doing.",
  ])

  <section class="py-16 px-4">
    <div class="max-w-5xl mx-auto">
      <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Why Work With Us</h2>
      <div class="grid gap-5 md:grid-cols-3 mb-16">
        @foreach([
          ['icon' => 'fa-graduation-cap', 'title' => 'Meaningful impact', 'text' => 'Your work helps schools and universities get the resources their learners need.'],
          ['icon' => 'fa-laptop-house', 'title' => 'Flexible work', 'text' => 'We care about outcomes, not hours at a desk.'],
          ['icon' => 'fa-seedling', 'title' => 'Room to grow', 'text' => 'A growing platform means real ownership and new challenges.'],
        ] as $perk)
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4"><i class="fa-solid {{ $perk['icon'] }}"></i></div>
            <h3 class="font-semibold text-gray-900 mb-1.5">{{ $perk['title'] }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $perk['text'] }}</p>
          </div>
        @endforeach
      </div>

      <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center">
        <div class="w-12 h-12 mx-auto rounded-full bg-white border border-gray-200 text-gray-400 flex items-center justify-center mb-4"><i class="fa-solid fa-briefcase"></i></div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">No open positions right now</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">We're not actively hiring at the moment, but we're always happy to hear from talented people. Send us your details and we'll be in touch when a role opens up.</p>
        <a href="{{ route('v2.pages.contact') }}" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-6 py-3 rounded-lg transition">Get in Touch</a>
        @if($contactEmail)
          <p class="text-xs text-gray-400 mt-4">or email <a href="mailto:{{ $contactEmail }}" class="text-emerald-600 hover:underline">{{ $contactEmail }}</a></p>
        @endif
      </div>
    </div>
  </section>

@endsection
