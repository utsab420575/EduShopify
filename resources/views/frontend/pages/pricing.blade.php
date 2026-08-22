@extends('frontend.layouts.master')

@section('title', 'Supplier Pricing — EduShopify')
@section('meta_description', 'Compare EduShopify subscription plans for suppliers.')

@section('content')
    <div class="fe-container py-10 sm:py-14">
        <x-frontend::navigation.breadcrumbs :items="['Pricing' => null]" />

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Plans for every supplier</h1>
            <p class="mt-3 text-base" style="color:var(--fe-text-muted);">Transparent pricing to help you reach institutional buyers on EduShopify.</p>
        </div>

        @if($plans->isEmpty())
            <x-frontend::common.empty-state icon="fa-tags" title="No plans available right now" description="Check back soon." />
        @else
            @php
                $planGridClass = match (min($plans->count(), 4)) {
                    1 => 'lg:grid-cols-1',
                    2 => 'lg:grid-cols-2',
                    3 => 'lg:grid-cols-3',
                    default => 'lg:grid-cols-4',
                };
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $planGridClass }} gap-5 max-w-6xl mx-auto mb-14">
                @foreach($plans as $plan)
                    <div class="fe-card rounded-2xl p-6 flex flex-col {{ $plan->is_featured ? 'ring-2' : '' }}" @if($plan->is_featured) style="--tw-ring-color:var(--fe-primary);" @endif>
                        @if($plan->is_featured)
                            <span class="self-start mb-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" style="background:var(--fe-primary);color:#fff;">Recommended</span>
                        @endif
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--fe-primary);">{{ ucfirst($plan->billing_type) }}</p>
                        <h3 class="text-lg font-bold mb-1" style="color:var(--fe-text);font-family:var(--font-display);">{{ $plan->name }}</h3>
                        <p class="text-3xl font-bold mb-1" style="color:var(--fe-text);">{{ $plan->formattedPrice() }}</p>
                        @if($plan->trial_days > 0)
                            <p class="text-xs mb-4" style="color:var(--fe-text-muted);">{{ $plan->trial_days }}-day free trial</p>
                        @else
                            <p class="text-xs mb-4">&nbsp;</p>
                        @endif

                        <ul class="space-y-2 text-sm mb-6 flex-1" style="color:var(--fe-text-muted);">
                            @if($plan->max_active_listings)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_active_listings }} active listings</li>@endif
                            @if($plan->max_products)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_products }} products</li>@endif
                            @if($plan->max_services)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_services }} services</li>@endif
                            @if($plan->max_team_members)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_team_members }} team members</li>@endif
                            @if($plan->max_monthly_quotations)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_monthly_quotations }} quotations/month</li>@endif
                            @if($plan->has_rfq_notifications)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>RFQ notifications</li>@endif
                            @if($plan->has_analytics)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Analytics dashboard</li>@endif
                            @if($plan->has_verified_badge)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Verified Supplier badge</li>@endif
                            @if($plan->has_homepage_placement)<li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Homepage placement eligibility</li>@endif
                        </ul>

                        @auth
                            @if((auth()->user()->accountMember?->account)?->isSupplier())
                                <a href="{{ route('supplier.pricing') }}" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Manage Subscription</a>
                            @else
                                <a href="{{ route('register') }}" class="fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Become a Supplier</a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Become a Supplier</a>
                        @endauth
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-center">
            <a href="{{ route('frontend.pages.faqs') }}" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">Have questions? See our FAQs &rarr;</a>
        </div>
    </div>
@endsection
