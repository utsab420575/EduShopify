@extends('frontend_new.layouts.app')

@section('title', 'About Us – Connecting Education Buyers with Trusted Suppliers | Edushopify')

@section('content')

  @include('frontend_new.pages.partials._hero', [
    'label' => 'About Edushopify',
    'title' => "Connecting the World's Education Buyers with Trusted Suppliers",
    'subtitle' => 'Edushopify is a B2B procurement marketplace built for schools, universities and training institutions — making it simple to discover verified suppliers, request quotations and compare offers in one place.',
  ])

  {{-- Mission & Vision --}}
  <section class="py-16 px-4">
    <div class="max-w-5xl mx-auto grid gap-10 lg:grid-cols-2">
      <div>
        <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4"><i class="fa-solid fa-bullseye"></i></div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Our Mission</h2>
        <p class="text-gray-600 leading-relaxed">To make education procurement transparent and efficient — giving institutional buyers a structured way to source what they need, and giving suppliers a fair place to compete on quality and value.</p>
      </div>
      <div class="lg:border-l lg:border-gray-200 lg:pl-10">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4"><i class="fa-solid fa-eye"></i></div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Our Vision</h2>
        <p class="text-gray-600 leading-relaxed">A connected global education marketplace where every institution, regardless of size or location, can reach trusted suppliers and every learner benefits from better resources.</p>
      </div>
    </div>
  </section>

  {{-- Live platform stats --}}
  <section class="bg-white border-y border-gray-100 py-10 px-4">
    <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      @foreach($stats as $stat)
        <div>
          <p class="text-3xl font-bold text-gray-900">{{ number_format($stat['value']) }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Values --}}
  <section class="bg-gray-50 py-16 px-4">
    <div class="max-w-5xl mx-auto">
      <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">What We Stand For</h2>
      <div class="grid gap-5 md:grid-cols-3">
        @foreach([
          ['icon' => 'fa-shield-halved', 'title' => 'Trust', 'text' => 'Suppliers are reviewed and approved by our team before they are marked as verified on the marketplace.'],
          ['icon' => 'fa-scale-balanced', 'title' => 'Transparency', 'text' => 'Structured RFQs and side-by-side quotation comparison help buyers make clear, well-documented decisions.'],
          ['icon' => 'fa-earth-americas', 'title' => 'Access', 'text' => 'Institutions of every size can reach suppliers across countries without long, informal sourcing chains.'],
        ] as $value)
          <div class="bg-white rounded-lg p-6">
            <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4"><i class="fa-solid {{ $value['icon'] }}"></i></div>
            <h3 class="font-semibold text-gray-900 mb-1.5">{{ $value['title'] }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $value['text'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA --}}
  <section class="bg-gray-900 text-white text-center py-16 px-4">
    <h2 class="text-2xl sm:text-3xl font-bold mb-6">Ready to Grow Your Education Business?</h2>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
      <a href="{{ route('register') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-6 py-3 rounded-lg transition">Register as Supplier</a>
      <a href="{{ route('v2.suppliers.index') }}" class="border border-white/70 hover:bg-white hover:text-gray-900 text-white text-sm font-semibold px-6 py-3 rounded-lg transition">Browse Suppliers</a>
    </div>
  </section>

@endsection
