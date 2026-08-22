@if($openRfqOpportunities->isNotEmpty())
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Sourcing"
                title="Open RFQ opportunities"
                subtitle="Live buyer requests currently accepting quotations."
                :action="route('frontend.rfqs.index')"
                actionLabel="View all opportunities"
            />

            <div class="space-y-3">
                @foreach($openRfqOpportunities as $opportunity)
                    <x-frontend::marketplace.rfq-card :opportunity="$opportunity" />
                @endforeach
            </div>

            <div class="mt-6 text-center text-sm" style="color:var(--fe-text-muted);">
                Ready to quote? <a href="{{ route('login') }}" class="font-semibold" style="color:var(--fe-primary);">Log in or register as a Supplier</a> to submit a quotation.
            </div>
        </div>
    </section>
@endif
