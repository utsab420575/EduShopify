@props(['opportunity'])

@php
    $location = collect([$opportunity->delivery_city, $opportunity->delivery_state, $opportunity->delivery_country])->filter()->implode(', ');
    $closesInDays = $opportunity->quotation_deadline ? now()->diffInDays($opportunity->quotation_deadline, false) : null;
@endphp

<div class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <x-frontend::common.badge variant="verified"><span class="w-1.5 h-1.5 rounded-full" style="background:var(--fe-primary);"></span> Open</x-frontend::common.badge>
            <span class="text-xs font-mono" style="color:var(--fe-text-subtle);">{{ $opportunity->rfq_number }}</span>
        </div>
        <a href="{{ route('frontend.rfqs.show', $opportunity->rfq_number) }}" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-text);">{{ $opportunity->title }}</a>
        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs" style="color:var(--fe-text-muted);">
            @if($opportunity->category_summary)
                <span><i class="fa-solid fa-tag text-[10px] mr-1"></i>{{ Str::limit($opportunity->category_summary, 40) }}</span>
            @endif
            @if($location)
                <span><i class="fa-solid fa-location-dot text-[10px] mr-1"></i>{{ $location }}</span>
            @endif
            @if($opportunity->item_count)
                <span><i class="fa-solid fa-list text-[10px] mr-1"></i>{{ $opportunity->item_count }} {{ Str::plural('item', $opportunity->item_count) }}</span>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between sm:flex-col sm:items-end gap-2 shrink-0">
        <div class="text-right">
            <p class="text-xs" style="color:var(--fe-text-muted);">Quotation deadline</p>
            <p class="text-sm font-semibold" style="color:var(--fe-text);">
                {{ $opportunity->quotation_deadline?->format('M j, Y') ?? '—' }}
                @if($closesInDays !== null && $closesInDays >= 0)
                    <span class="block text-[11px] font-normal" style="color:var(--fe-warning);">Closes in {{ $closesInDays }} {{ Str::plural('day', $closesInDays) }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('frontend.rfqs.show', $opportunity->rfq_number) }}" class="fe-focus-ring shrink-0 text-xs font-semibold px-3.5 py-2 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
            View Summary
        </a>
    </div>
</div>
