@if(($stats['suppliers'] ?? 0) > 0 || ($stats['listings'] ?? 0) > 0)
    <section class="py-12" style="background:var(--fe-canvas);">
        <div class="fe-container">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 text-center max-w-3xl mx-auto">
                @if(($stats['suppliers'] ?? 0) > 0)
                    <div>
                        <p class="text-3xl font-bold" style="font-family:var(--font-display);color:var(--fe-primary);">{{ $stats['suppliers'] }}</p>
                        <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Verified Suppliers</p>
                    </div>
                @endif
                @if(($stats['listings'] ?? 0) > 0)
                    <div>
                        <p class="text-3xl font-bold" style="font-family:var(--font-display);color:var(--fe-primary);">{{ $stats['listings'] }}</p>
                        <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Published Listings</p>
                    </div>
                @endif
                @if(($stats['categories'] ?? 0) > 0)
                    <div>
                        <p class="text-3xl font-bold" style="font-family:var(--font-display);color:var(--fe-primary);">{{ $stats['categories'] }}</p>
                        <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Active Categories</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
