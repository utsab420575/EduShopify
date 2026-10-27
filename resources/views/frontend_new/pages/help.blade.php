@extends('frontend_new.layouts.app')

@section('title', 'Help Center | Edushopify')

@section('content')

  @php
    $faqGroups = [
      'Getting Started' => [
        ['q' => 'How do I register as a Buyer?', 'a' => 'Click Register, choose the Buyer capability, and complete your account and organization details.'],
        ['q' => 'Is registration free for Buyers?', 'a' => 'Yes. Creating a Buyer account, browsing suppliers and posting RFQs is free.'],
      ],
      'Supplier Verification' => [
        ['q' => 'How does Supplier verification work?', 'a' => 'After registering as a Supplier, complete your company profile and upload the required documents. Our team reviews them before your account is approved and shown as verified.'],
        ['q' => 'How long does approval take?', 'a' => 'Review times vary with volume, but our team reviews applications as quickly as possible and notifies you of the outcome.'],
      ],
      'RFQs & Quotations' => [
        ['q' => 'What is an RFQ?', 'a' => 'A Request for Quotation is a structured sourcing request a Buyer posts describing what they need. Suppliers respond with quotations the Buyer can compare.'],
        ['q' => 'Can I submit a quotation without an account?', 'a' => 'No. Submitting an official quotation requires an approved Supplier account with an active subscription.'],
        ['q' => 'Is my RFQ visible to everyone?', 'a' => 'Only RFQs marked as globally visible appear on the public RFQ board, and only as a safe summary. RFQs sent to selected suppliers are never shown publicly.'],
      ],
      'Subscriptions & Payments' => [
        ['q' => 'Do Suppliers need a subscription?', 'a' => 'Yes. Suppliers choose a subscription plan that sets listing limits, RFQ access and features. See the Pricing page for current plans.'],
        ['q' => 'Does Edushopify process product payments?', 'a' => 'Not at the moment. Payment for products and services happens directly between Buyer and Supplier. Edushopify processes Supplier subscription payments only.'],
      ],
    ];
  @endphp

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Help Center',
    'title' => 'How Can We Help?',
    'subtitle' => 'Answers to common questions about buying, selling and managing your account.',
  ])

  <section class="py-14 px-4" x-data="{ open: null, q: '' }">
    <div class="max-w-3xl mx-auto">

      <div class="relative mb-10">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input type="search" x-model="q" placeholder="Search help articles…"
               class="w-full rounded-xl border border-gray-300 bg-white pl-11 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
      </div>

      <div class="space-y-10">
        @foreach($faqGroups as $group => $items)
          <div x-show="q === '' || {{ Js::from(Str::lower(collect($items)->flatten()->implode(' '))) }}.includes(q.toLowerCase())">
            <h2 class="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-3">{{ $group }}</h2>
            <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-200">
              @foreach($items as $item)
                @php $key = $loop->parent->index.'-'.$loop->index; @endphp
                <div x-show="q === '' || {{ Js::from(Str::lower($item['q'].' '.$item['a'])) }}.includes(q.toLowerCase())">
                  <button type="button" @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                          class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left text-sm font-medium text-gray-900 hover:bg-gray-50 transition">
                    <span>{{ $item['q'] }}</span>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform" :class="open === '{{ $key }}' && 'rotate-180'"></i>
                  </button>
                  <div x-show="open === '{{ $key }}' || q !== ''" x-cloak class="px-5 pb-4 text-sm text-gray-600 leading-relaxed">{{ $item['a'] }}</div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-14 grid gap-4 sm:grid-cols-3 text-center">
        <a href="{{ route('v2.pages.pricing') }}" class="border border-gray-200 rounded-xl p-5 hover:border-emerald-300 transition">
          <i class="fa-solid fa-tags text-emerald-600 mb-2"></i>
          <p class="text-sm font-semibold text-gray-900">Pricing</p>
        </a>
        <a href="{{ route('v2.blogs.index') }}" class="border border-gray-200 rounded-xl p-5 hover:border-emerald-300 transition">
          <i class="fa-solid fa-newspaper text-emerald-600 mb-2"></i>
          <p class="text-sm font-semibold text-gray-900">Blog</p>
        </a>
        <a href="{{ route('v2.pages.contact') }}" class="border border-gray-200 rounded-xl p-5 hover:border-emerald-300 transition">
          <i class="fa-solid fa-headset text-emerald-600 mb-2"></i>
          <p class="text-sm font-semibold text-gray-900">Contact Support</p>
        </a>
      </div>
    </div>
  </section>

@endsection
