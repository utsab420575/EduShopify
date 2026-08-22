<section class="py-12 lg:py-16" style="background:var(--fe-canvas);">
    <div class="fe-container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="rounded-2xl p-8 sm:p-10 text-white" style="background:var(--fe-primary);">
                <h3 class="text-2xl font-bold mb-2" style="font-family:var(--font-display);">Sourcing for your institution?</h3>
                <p class="text-sm opacity-90 mb-6 max-w-md">Post an RFQ and receive competitive quotations from verified suppliers.</p>
                <a href="{{ route('frontend.handoff.post-rfq') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold bg-white" style="color:var(--fe-primary);">
                    Post an RFQ <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="rounded-2xl p-8 sm:p-10 text-white" style="background:var(--fe-dark);">
                <h3 class="text-2xl font-bold mb-2" style="font-family:var(--font-display);">Sell to education institutions</h3>
                <p class="text-sm opacity-80 mb-6 max-w-md">Reach verified buyers actively sourcing products and services for education.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold border border-white/30 hover:bg-white/10">
                    Become a Supplier <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>
