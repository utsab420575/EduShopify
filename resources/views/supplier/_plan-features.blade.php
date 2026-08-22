{{-- Reusable plan feature list partial --}}
<ul class="space-y-2.5 border-t border-slate-100 pt-6 text-xs text-slate-600 font-medium">

    {{-- Listings --}}
    <li class="flex items-center gap-2">
        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
        @if($plan->max_active_listings === 0)
            Unlimited Listings
        @else
            Up to {{ number_format($plan->max_active_listings) }} Listing{{ $plan->max_active_listings > 1 ? 's' : '' }}
        @endif
    </li>

    {{-- Products --}}
    <li class="flex items-center gap-2">
        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
        @if($plan->max_products === 0)
            Unlimited Products
        @else
            Up to {{ number_format($plan->max_products) }} Product{{ $plan->max_products > 1 ? 's' : '' }}
        @endif
    </li>

    {{-- RFQ Access --}}
    <li class="flex items-center gap-2">
        @if($plan->rfq_delay_minutes === 0)
            <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
            Instant RFQ Access
        @else
            <span class="text-slate-300 font-bold flex-shrink-0">✓</span>
            RFQ Access
            <span class="text-slate-400">({{ $plan->rfq_delay_minutes >= 60 ? floor($plan->rfq_delay_minutes/60).'hr' : $plan->rfq_delay_minutes.'min' }} delay)</span>
        @endif
    </li>

    {{-- Notifications --}}
    <li class="flex items-center gap-2 {{ $plan->has_rfq_notifications ? '' : 'opacity-40' }}">
        <span class="{{ $plan->has_rfq_notifications ? 'text-emerald-500' : 'text-slate-300' }} font-bold flex-shrink-0">
            {{ $plan->has_rfq_notifications ? '✓' : '✗' }}
        </span>
        RFQ Email Notifications
    </li>

    {{-- Analytics --}}
    <li class="flex items-center gap-2 {{ $plan->has_analytics ? '' : 'opacity-40' }}">
        <span class="{{ $plan->has_analytics ? 'text-emerald-500' : 'text-slate-300' }} font-bold flex-shrink-0">
            {{ $plan->has_analytics ? '✓' : '✗' }}
        </span>
        Analytics Dashboard
    </li>

    {{-- Verified Badge --}}
    <li class="flex items-center gap-2 {{ $plan->has_verified_badge ? '' : 'opacity-40' }}">
        <span class="{{ $plan->has_verified_badge ? 'text-emerald-500' : 'text-slate-300' }} font-bold flex-shrink-0">
            {{ $plan->has_verified_badge ? '✓' : '✗' }}
        </span>
        Verified Supplier Badge
    </li>

    {{-- Homepage Placement --}}
    <li class="flex items-center gap-2 {{ $plan->has_homepage_placement ? '' : 'opacity-40' }}">
        <span class="{{ $plan->has_homepage_placement ? 'text-emerald-500' : 'text-slate-300' }} font-bold flex-shrink-0">
            {{ $plan->has_homepage_placement ? '✓' : '✗' }}
        </span>
        Featured Homepage Placement
    </li>

    {{-- Team Members --}}
    <li class="flex items-center gap-2 {{ $plan->has_team_members ? '' : 'opacity-40' }}">
        <span class="{{ $plan->has_team_members ? 'text-emerald-500' : 'text-slate-300' }} font-bold flex-shrink-0">
            {{ $plan->has_team_members ? '✓' : '✗' }}
        </span>
        Multiple Team Members
    </li>

</ul>
