@if($featuredPlans->isNotEmpty())
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Supplier Pricing"
                title="Plans for every supplier"
                subtitle="Choose the plan that fits your business, with transparent pricing."
                :action="route('frontend.pages.pricing')"
                actionLabel="View full pricing"
                align="center"
            />

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 max-w-4xl mx-auto">
                @foreach($featuredPlans as $plan)
                    <div class="fe-card rounded-2xl p-6 {{ $plan->is_featured ? 'ring-2' : '' }}" @if($plan->is_featured) style="--tw-ring-color:var(--fe-primary);" @endif>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--fe-primary);">{{ ucfirst($plan->billing_type) }}</p>
                        <h3 class="text-lg font-bold mb-1" style="color:var(--fe-text);font-family:var(--font-display);">{{ $plan->name }}</h3>
                        <p class="text-2xl font-bold mb-4" style="color:var(--fe-text);">{{ $plan->formattedPrice() }}</p>
                        <ul class="space-y-1.5 text-sm mb-5" style="color:var(--fe-text-muted);">
                            @if($plan->max_active_listings)
                                <li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_active_listings }} active listings</li>
                            @endif
                            @if($plan->max_team_members)
                                <li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>{{ $plan->max_team_members }} team members</li>
                            @endif
                            @if($plan->has_verified_badge)
                                <li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Verified Supplier badge</li>
                            @endif
                            @if($plan->has_analytics)
                                <li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Analytics dashboard</li>
                            @endif
                        </ul>
                        <a href="{{ route('frontend.pages.pricing') }}" class="fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                            View Pricing
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
