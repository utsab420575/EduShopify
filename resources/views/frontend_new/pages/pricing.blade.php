@extends('frontend_new.layouts.app')

@section('title', 'Pricing – Supplier Subscription Plans | Edushopify')

@section('content')

  @php
    // 9999+ is how admins enter "no practical limit" on a plan.
    $limit = fn ($n) => $n >= 9999 ? 'Unlimited' : number_format($n);

    $isSupplier = auth()->check() && auth()->user()->isSupplier();
    $ctaUrl = $isSupplier ? route('supplier.subscription.plans') : route('register');

    $gridCols = match (min($plans->count(), 4)) {
      1 => 'md:grid-cols-1 max-w-sm',
      2 => 'md:grid-cols-2 max-w-3xl',
      3 => 'md:grid-cols-3 max-w-6xl',
      default => 'md:grid-cols-2 lg:grid-cols-4 max-w-7xl',
    };
  @endphp

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Pricing',
    'title' => 'Simple, Transparent Pricing',
    'subtitle' => 'Buyers use Edushopify for free. Suppliers choose the plan that fits their business — upgrade any time.',
  ])

  <section class="py-16 px-4">
    @if($plans->isEmpty())
      <div class="max-w-md mx-auto text-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-10">
        <p class="font-semibold text-gray-900 mb-1">Plans coming soon</p>
        <p class="text-sm text-gray-500">Subscription plans are being finalised. <a href="{{ route('v2.pages.contact') }}" class="text-emerald-600 hover:underline">Contact us</a> for details.</p>
      </div>
    @else
      <div class="grid grid-cols-1 {{ $gridCols }} gap-6 mx-auto items-stretch">
        @foreach($plans as $plan)
          @php
            $features = collect([
              $plan->max_active_listings ? $limit($plan->max_active_listings).' active listings' : null,
              $plan->max_products ? $limit($plan->max_products).' products' : null,
              $plan->max_services ? $limit($plan->max_services).' services' : null,
              $plan->max_monthly_quotations ? $limit($plan->max_monthly_quotations).' quotations / month' : null,
              $plan->has_team_members && $plan->max_team_members ? $limit($plan->max_team_members).' team members' : null,
            ])->filter()->map(fn ($text) => ['text' => $text, 'on' => true])
              ->merge([
                ['text' => 'RFQ notifications', 'on' => $plan->has_rfq_notifications],
                ['text' => 'Analytics dashboard', 'on' => $plan->has_analytics],
                ['text' => 'Verified Supplier badge', 'on' => $plan->has_verified_badge],
                ['text' => 'Homepage placement', 'on' => $plan->has_homepage_placement],
              ])
              ->merge(collect(is_array($plan->features) ? $plan->features : [])
                ->filter(fn ($f) => is_string($f) && $f !== '')
                ->map(fn ($f) => ['text' => $f, 'on' => true]));
          @endphp

          <div class="relative flex flex-col bg-white rounded-2xl p-6 {{ $plan->is_featured ? 'border-2 border-emerald-500 shadow-lg' : 'border border-gray-200' }}">
            @if($plan->is_featured)
              <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-[11px] font-semibold uppercase tracking-wide px-3 py-1 rounded-full">Most Popular</span>
            @endif

            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-2">{{ $plan->isFree() ? 'Free' : ucfirst($plan->billing_type) }}</p>
            <h2 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h2>

            <div class="mt-4 mb-1 flex items-baseline gap-1">
              <span class="text-4xl font-bold text-gray-900">{{ $plan->formattedPrice() }}</span>
              @unless($plan->isFree())
                <span class="text-sm text-gray-500">/ {{ $plan->isYearly() ? 'year' : 'month' }}</span>
              @endunless
            </div>
            <p class="text-xs text-gray-400 h-4 mb-6">
              @if($plan->trial_days > 0 && ! $plan->isFree())
                {{ $plan->trial_days }}-day free trial
              @elseif($plan->isFree())
                Free forever
              @endif
            </p>

            <ul class="space-y-3 text-sm flex-1 mb-8">
              @foreach($features as $feature)
                <li class="flex items-start gap-2.5 {{ $feature['on'] ? 'text-gray-700' : 'text-gray-400' }}">
                  <i class="fa-solid {{ $feature['on'] ? 'fa-check text-emerald-500' : 'fa-xmark text-gray-300' }} text-xs mt-1 w-3"></i>
                  <span>{{ $feature['text'] }}</span>
                </li>
              @endforeach
            </ul>

            <a href="{{ $ctaUrl }}" class="block text-center text-sm font-semibold px-4 py-3 rounded-lg transition {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'border border-gray-300 text-gray-900 hover:bg-gray-50' }}">
              {{ $isSupplier ? 'Choose Plan' : ($plan->isFree() ? 'Get Started Free' : 'Become a Supplier') }}
            </a>
          </div>
        @endforeach
      </div>
    @endif

    <p class="text-center text-sm text-gray-500 mt-12">
      Have questions? Visit the <a href="{{ route('v2.pages.help') }}" class="font-semibold text-emerald-600 hover:underline">Help Center</a>
      or <a href="{{ route('v2.pages.contact') }}" class="font-semibold text-emerald-600 hover:underline">contact us</a>.
    </p>
  </section>

@endsection
