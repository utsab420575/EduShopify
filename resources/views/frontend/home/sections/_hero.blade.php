<section class="relative overflow-hidden" style="background:linear-gradient(180deg,#F0F9F5 0%,#F8FAFC 100%);">
    <div class="fe-container py-16 sm:py-20 lg:py-24">
        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold" style="background:var(--fe-surface);border:1px solid var(--fe-border);color:var(--fe-primary);">
                <span class="w-1.5 h-1.5 rounded-full" style="background:var(--fe-primary);"></span>
                B2B Education Procurement Marketplace
            </span>

            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-[52px] font-bold tracking-tight leading-[1.1]" style="font-family:var(--font-display);color:var(--fe-text);">
                Source smarter for your <span style="color:var(--fe-primary);">institution</span>
            </h1>

            <p class="mt-5 text-base sm:text-lg max-w-2xl mx-auto" style="color:var(--fe-text-muted);">
                Discover verified suppliers, request competitive quotations, and manage procurement for your school, college or university — all in one structured marketplace.
            </p>

            <div class="mt-8 max-w-2xl mx-auto bg-white rounded-2xl border p-2 shadow-sm" style="border-color:var(--fe-border);">
                <form method="GET" action="{{ route('frontend.catalog.index') }}" class="flex flex-col sm:flex-row gap-2" role="search">
                    <label for="fe-hero-search" class="sr-only">Search the marketplace</label>
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" id="fe-hero-search" name="q" placeholder="Search laboratory equipment, IT hardware, training..." class="fe-focus-ring w-full h-14 pl-12 pr-4 rounded-xl text-base border-0" style="background:transparent;">
                    </div>
                    <button type="submit" class="fe-btn-primary fe-focus-ring h-14 px-6 rounded-xl text-sm font-semibold shrink-0">
                        Search Marketplace
                    </button>
                </form>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('frontend.handoff.post-rfq') }}" class="fe-btn-primary fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold">
                    Post an RFQ
                </a>
                <a href="{{ route('register') }}" class="fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold border bg-white" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                    Become a Supplier
                </a>
            </div>

            @if($topCategories->isNotEmpty())
                <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
                    @foreach($topCategories->take(6) as $cat)
                        <a href="{{ route('frontend.categories.show', $cat->slug) }}" class="fe-focus-ring px-3.5 py-1.5 rounded-full text-xs font-medium bg-white border hover:border-[--fe-primary] hover:text-[--fe-primary] transition-colors" style="border-color:var(--fe-border);color:var(--fe-text-muted);">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
