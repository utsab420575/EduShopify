@php
  // Admin-editable via admin.system.hero-section.edit (System UI Settings ▸
  // Hero Section), backed by the generic Setting key-value store (group:
  // homepage_hero) — see HomepageHeroController. Defaults below match what
  // shipped before that admin screen existed.
  $heroSettings = \App\Models\Setting::group('homepage_hero');
  $heroHeading = $heroSettings['heading'] ?? "Global Suppliers for\nEducation. All in One Place.";
  $heroSubheading = $heroSettings['subheading'] ?? 'Connect with verified education suppliers worldwide';
  $heroImagePath = $heroSettings['image_path'] ?? 'System_Files/HeroSection/Hero1.png';
  $heroPrimaryText = $heroSettings['primary_button_text'] ?? 'Find Suppliers';
  $heroPrimaryUrl = $heroSettings['primary_button_url'] ?? route('v2.suppliers.index');
  $heroSecondaryText = $heroSettings['secondary_button_text'] ?? 'Post an RFQ';
  $heroSecondaryUrl = $heroSettings['secondary_button_url'] ?? route('v2.handoff.post-rfq');
@endphp
<section class="relative shrink-0 h-[clamp(300px,calc(100dvh_-_var(--header-h,89px)_-_355px),620px)] overflow-hidden">
  {{-- Height is derived from the viewport, not a fixed breakpoint ladder:
       it's whatever is left after the header and Featured Suppliers'
       ~355px natural height (card row + its trimmed chrome), clamped to
       300-620px so it never gets too cramped or too tall. This is what
       keeps header + hero + Featured Suppliers fitting one screen on short
       laptop heights (e.g. 768px) instead of only reacting to width.
       As a side effect it also shortens on narrow/short phones, which
       reduces object-cover cropping on this very wide (2.74:1) source
       image — object-position below is biased right, where the sharpest
       subject sits and the overlay gradient is lightest. A dedicated
       mobile-cropped asset would still be the ideal long-term fix. --}}
  <img src="{{ asset('storage/' . $heroImagePath) }}" alt="Global Education Suppliers" class="absolute inset-0 w-full h-full object-cover object-[70%_center] sm:object-[65%_center] md:object-center" />
  <div class="hero-overlay absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-6 h-full flex flex-col justify-center hero-fade-up">
    <h1 class="text-white font-extrabold text-3xl sm:text-4xl md:text-5xl lg:text-[46px] leading-tight max-w-2xl">
      {!! nl2br(e($heroHeading)) !!}
    </h1>
    <p class="text-white/85 mt-3 text-sm sm:text-base font-normal max-w-md">{{ $heroSubheading }}</p>
    <div class="flex flex-wrap items-center gap-3 mt-7">
      <a href="{{ $heroPrimaryUrl }}" class="hero-btn-find inline-flex items-center justify-center bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-lg text-sm hover:bg-emerald-600 hover:text-white focus:bg-emerald-600 focus:text-white focus:outline-none focus:ring-2 focus:ring-white/80 transition-all duration-200 shadow-sm">
        {{ $heroPrimaryText }}
      </a>
      <a href="{{ $heroSecondaryUrl }}" class="inline-flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 active:bg-emerald-700 transition-all duration-200 shadow-sm">
        {{ $heroSecondaryText }}
      </a>
    </div>
  </div>
</section>
