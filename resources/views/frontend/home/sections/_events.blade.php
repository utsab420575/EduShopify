{{--
    Placeholder content: this app has no Event model/table yet, so these
    4 cards are static illustrative copy, not real events. Replace with a
    real $events collection from the controller once an Events feature
    exists on the backend.
--}}
@php
    $placeholderEvents = [
        ['title' => 'STEM & Robotics Expo 2026', 'category' => 'Expo', 'date' => 'Mar 12, 2026', 'location' => 'Dubai, UAE'],
        ['title' => 'EdTech Procurement Summit', 'category' => 'Summit', 'date' => 'Apr 8, 2026', 'location' => 'London, UK'],
        ['title' => 'Campus Furniture & AV Showcase', 'category' => 'Showcase', 'date' => 'May 20, 2026', 'location' => 'Singapore'],
        ['title' => 'Lab Equipment Buyers Meetup', 'category' => 'Meetup', 'date' => 'Jun 3, 2026', 'location' => 'Nairobi, Kenya'],
    ];
@endphp

<section class="py-12 lg:py-16 bg-white">
    <div class="fe-container">
        <x-frontend::common.section-heading
            eyebrow="Don't Miss Out"
            title="Education STEM & Robotics Events"
        />

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($placeholderEvents as $event)
                <div class="relative h-56 rounded-lg overflow-hidden group">
                    <img src="{{ asset('images/herosection.png') }}" alt="{{ $event['title'] }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    <div class="absolute inset-0 bg-black/50"></div>
                    <span class="absolute top-3 left-3 bg-emerald-500 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded">{{ $event['category'] }}</span>
                    <div class="absolute bottom-3 left-3 right-3">
                        <p class="text-white font-semibold text-sm fe-line-clamp-2">{{ $event['title'] }}</p>
                        <p class="text-white/70 text-xs mt-1">{{ $event['date'] }} · {{ $event['location'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
