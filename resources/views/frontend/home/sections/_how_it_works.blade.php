<section class="py-12 lg:py-16" style="background:var(--fe-canvas);" x-data="{ track: 'buyer' }">
    <div class="fe-container">
        <x-frontend::common.section-heading
            eyebrow="Process"
            title="How EduShopify works"
            align="center"
        />

        <div class="flex justify-center mb-10">
            <div class="inline-flex items-center bg-white rounded-xl p-1 gap-1 border" style="border-color:var(--fe-border);">
                <button @click="track = 'buyer'" :class="track === 'buyer' ? 'text-white' : 'text-slate-600'" :style="track === 'buyer' ? 'background:var(--fe-primary)' : ''" class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors">Buyer Journey</button>
                <button @click="track = 'supplier'" :class="track === 'supplier' ? 'text-white' : 'text-slate-600'" :style="track === 'supplier' ? 'background:var(--fe-primary)' : ''" class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors">Supplier Journey</button>
            </div>
        </div>

        <div x-show="track === 'buyer'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach([
                ['icon' => 'fa-magnifying-glass', 'title' => 'Discover', 'text' => 'Browse products, services and suppliers.'],
                ['icon' => 'fa-file-lines', 'title' => 'Create RFQ', 'text' => 'Post a structured request for quotation.'],
                ['icon' => 'fa-inbox', 'title' => 'Receive Quotations', 'text' => 'Suppliers respond with competitive offers.'],
                ['icon' => 'fa-scale-balanced', 'title' => 'Compare', 'text' => 'Shortlist or request revisions.'],
                ['icon' => 'fa-trophy', 'title' => 'Award', 'text' => 'Award the winning supplier.'],
                ['icon' => 'fa-file-signature', 'title' => 'Purchase Order', 'text' => 'A purchase order is generated.'],
            ] as $i => $step)
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3 relative" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                        <i class="fa-solid {{ $step['icon'] }}"></i>
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-slate-800 text-white text-[10px] flex items-center justify-center font-semibold">{{ $i + 1 }}</span>
                    </div>
                    <p class="text-sm font-semibold" style="color:var(--fe-text);">{{ $step['title'] }}</p>
                    <p class="text-xs mt-1" style="color:var(--fe-text-muted);">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div x-show="track === 'supplier'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-7 gap-6">
            @foreach([
                ['icon' => 'fa-user-plus', 'title' => 'Register', 'text' => 'Create your supplier account.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Verification', 'text' => 'Complete profile and documents.'],
                ['icon' => 'fa-credit-card', 'title' => 'Subscription', 'text' => 'Choose a plan that fits your business.'],
                ['icon' => 'fa-box-open', 'title' => 'Publish Listings', 'text' => 'List your products and services.'],
                ['icon' => 'fa-bell', 'title' => 'Get RFQ Access', 'text' => 'Receive eligible RFQ opportunities.'],
                ['icon' => 'fa-file-invoice-dollar', 'title' => 'Submit Quotations', 'text' => 'Respond with competitive pricing.'],
                ['icon' => 'fa-handshake', 'title' => 'Win Business', 'text' => 'Get awarded and fulfil the order.'],
            ] as $i => $step)
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3 relative" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                        <i class="fa-solid {{ $step['icon'] }}"></i>
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-slate-800 text-white text-[10px] flex items-center justify-center font-semibold">{{ $i + 1 }}</span>
                    </div>
                    <p class="text-sm font-semibold" style="color:var(--fe-text);">{{ $step['title'] }}</p>
                    <p class="text-xs mt-1" style="color:var(--fe-text-muted);">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('frontend.pages.how-it-works') }}" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">Learn more about how it works &rarr;</a>
        </div>
    </div>
</section>
