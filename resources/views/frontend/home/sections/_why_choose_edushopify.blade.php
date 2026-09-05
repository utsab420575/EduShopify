<section class="py-12 lg:py-16 bg-gray-50">
    <div class="fe-container text-center">
        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600">Our Advantage</p>
        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1.5 font-display">Why Choose Edushopify</h2>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mt-8 text-left">
            @foreach([
                ['icon' => 'fa-circle-check', 'title' => 'Verified Suppliers', 'text' => 'Every supplier is reviewed before appearing in the marketplace.'],
                ['icon' => 'fa-earth-americas', 'title' => 'Global Reach', 'text' => 'Source from institutions and vendors around the world.'],
                ['icon' => 'fa-file-invoice', 'title' => 'RFQ Opportunities', 'text' => 'Post requirements and receive comparable, competitive quotations.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Secure & Reliable', 'text' => 'Structured procurement workflows built for institutional buyers.'],
            ] as $item)
                <div>
                    <span class="w-12 h-12 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </span>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5">{{ $item['title'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
