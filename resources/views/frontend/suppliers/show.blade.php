@extends('frontend.layouts.master')

@section('title', $supplier->display_name.' — EduShopify')
@section('meta_description', Str::limit(strip_tags($supplier->description ?? ($supplier->display_name.' on EduShopify.')), 155))

@section('content')
    @php
        $location = collect([$supplier->city?->name, $supplier->state?->name, $supplier->country?->name])->filter()->implode(', ');
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    @endphp

    <div class="h-36 sm:h-44" style="background:linear-gradient(120deg,var(--fe-primary),#2D8A67);"></div>

    <div class="fe-container">
        <div class="-mt-12 sm:-mt-14 flex items-end gap-4 pb-6">
            <span class="w-24 h-24 rounded-2xl border-4 border-white flex items-center justify-center text-3xl font-bold shrink-0 bg-white shadow-sm" style="color:var(--fe-primary);font-family:var(--font-display);">
                {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
            </span>
            <div class="pb-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);">{{ $supplier->display_name }}</h1>
                    <x-frontend::common.badge variant="verified"><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier</x-frontend::common.badge>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm" style="color:var(--fe-text-muted);">
                    @if($location)<span><i class="fa-solid fa-location-dot text-xs mr-1"></i>{{ $location }}</span>@endif
                    @if($supplier->account?->supplierTypes->isNotEmpty())<span>{{ $supplier->account->supplierTypes->pluck('name')->implode(', ') }}</span>@endif
                    <x-frontend::marketplace.rating-summary :rating="$supplier->rating" :count="$supplier->reviews_count ?? 0" />
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pb-6">
            @auth
                <a href="{{ route('buyer.rfqs.create', ['supplier_account_id' => $supplier->account_id]) }}" class="fe-btn-primary fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quote</a>
            @else
                <a href="{{ route('frontend.handoff.request-quote-supplier', $supplier->slug) }}" class="fe-btn-primary fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quote</a>
            @endauth
            <button type="button" @click="$dispatch('open-inquiry-supplier')" class="fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Contact Supplier</button>
            <a href="{{ auth()->check() ? '#' : route('frontend.handoff.save-supplier', $supplier->slug) }}"
               @if(auth()->check()) onclick="event.preventDefault(); document.getElementById('fe-save-supplier-form').submit();" @endif
               class="fe-focus-ring px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                <i class="fa-regular fa-bookmark mr-1.5"></i>Save Supplier
            </a>
            @auth
                <form id="fe-save-supplier-form" method="POST" action="{{ route('buyer.saved-items.toggle') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="type" value="supplier">
                    <input type="hidden" name="id" value="{{ $supplier->account_id }}">
                </form>
            @endauth
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-12">
            {{-- Left rail --}}
            <aside class="lg:col-span-3 space-y-4">
                <div class="fe-card rounded-2xl p-5 lg:sticky lg:top-24">
                    <h3 class="text-sm font-semibold mb-3" style="color:var(--fe-text);">Company Overview</h3>
                    <dl class="space-y-2.5 text-sm">
                        @if($supplier->founded_year)
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Founded</dt><dd style="color:var(--fe-text);">{{ $supplier->founded_year }}</dd></div>
                        @endif
                        @if($supplier->employees)
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Employees</dt><dd style="color:var(--fe-text);">{{ $supplier->employees }}</dd></div>
                        @endif
                        @if($supplier->quotation_response_rate)
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Response Rate</dt><dd style="color:var(--fe-text);">{{ round($supplier->quotation_response_rate) }}%</dd></div>
                        @endif
                        @if($supplier->average_response_minutes)
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Avg. Response</dt><dd style="color:var(--fe-text);">{{ $supplier->average_response_minutes < 60 ? $supplier->average_response_minutes.' min' : round($supplier->average_response_minutes / 60).' hr' }}</dd></div>
                        @endif
                        @if($supplier->website)
                            <div class="flex justify-between"><dt style="color:var(--fe-text-muted);">Website</dt><dd class="truncate max-w-[140px]"><a href="{{ $supplier->website }}" target="_blank" rel="noopener nofollow" class="hover:underline" style="color:var(--fe-primary);">{{ parse_url($supplier->website, PHP_URL_HOST) ?? $supplier->website }}</a></dd></div>
                        @endif
                    </dl>

                    @if($supplier->businessHours->isNotEmpty())
                        <h3 class="text-sm font-semibold mt-5 mb-2" style="color:var(--fe-text);">Business Hours</h3>
                        <ul class="space-y-1 text-xs" style="color:var(--fe-text-muted);">
                            @foreach($supplier->businessHours->sortBy('day_of_week') as $hour)
                                <li class="flex justify-between">
                                    <span>{{ $days[$hour->day_of_week] ?? '' }}</span>
                                    <span>{{ $hour->is_open ? ($hour->open_time.' – '.$hour->close_time) : 'Closed' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if($supplier->account?->exhibitions->isNotEmpty())
                        <h3 class="text-sm font-semibold mt-5 mb-2" style="color:var(--fe-text);">Exhibitions</h3>
                        <ul class="space-y-1 text-xs" style="color:var(--fe-text-muted);">
                            @foreach($supplier->account->exhibitions as $exhibition)
                                <li>{{ $exhibition->name }} @if($exhibition->pivot->participation_year)({{ $exhibition->pivot->participation_year }})@endif</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>

            {{-- Main --}}
            <div class="lg:col-span-9" x-data="{ section: 'catalog' }">
                <div class="flex items-center gap-1 border-b mb-6 overflow-x-auto" style="border-color:var(--fe-border);">
                    @foreach(['catalog' => 'Catalog', 'about' => 'About', 'reviews' => 'Reviews', 'gallery' => 'Gallery'] as $key => $label)
                        <button @click="section = '{{ $key }}'" :class="section === '{{ $key }}' ? 'font-semibold' : ''" :style="section === '{{ $key }}' ? 'color:var(--fe-primary);border-color:var(--fe-primary)' : 'color:var(--fe-text-muted);border-color:transparent'" class="px-4 py-2.5 text-sm border-b-2 whitespace-nowrap">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div x-show="section === 'catalog'">
                    <div class="flex items-center gap-2 mb-5">
                        @foreach(['all' => 'All', 'products' => 'Products ('.$productCount.')', 'services' => 'Services ('.$serviceCount.')'] as $key => $label)
                            <a href="{{ route('frontend.suppliers.show', [$supplier->slug, 'tab' => $key]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-medium {{ $tab === $key ? 'text-white' : 'border' }}" @if($tab === $key) style="background:var(--fe-primary);" @else style="border-color:var(--fe-border-strong);color:var(--fe-text-muted);" @endif>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    @if($listings->isEmpty())
                        <x-frontend::common.empty-state icon="fa-box-open" title="No listings published yet" />
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            @foreach($listings as $listing)
                                <x-frontend::marketplace.listing-card :listing="$listing" />
                            @endforeach
                        </div>
                        <x-frontend::common.pagination :paginator="$listings" />
                    @endif
                </div>

                <div x-show="section === 'about'" x-cloak>
                    <div class="fe-card rounded-2xl p-6">
                        @if($supplier->description)
                            <p class="text-sm leading-relaxed whitespace-pre-line" style="color:var(--fe-text-muted);">{{ $supplier->description }}</p>
                        @else
                            <p class="text-sm" style="color:var(--fe-text-muted);">This supplier has not added a company description yet.</p>
                        @endif
                    </div>
                </div>

                <div x-show="section === 'reviews'" x-cloak>
                    @if($reviews->isEmpty())
                        <x-frontend::common.empty-state icon="fa-star" title="No published reviews yet" />
                    @else
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="fe-card rounded-2xl p-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <x-frontend::marketplace.rating-summary :rating="$review->rating" :count="1" size="full" />
                                        <span class="text-xs" style="color:var(--fe-text-subtle);">{{ $review->published_at?->format('M j, Y') }}</span>
                                    </div>
                                    @if($review->title)<p class="text-sm font-semibold mb-1" style="color:var(--fe-text);">{{ $review->title }}</p>@endif
                                    <p class="text-sm" style="color:var(--fe-text-muted);">{{ $review->comment }}</p>
                                    <p class="text-xs mt-2" style="color:var(--fe-text-subtle);">{{ $review->buyerAccount?->display_name ?? 'Verified Buyer' }}</p>
                                    @if($review->reply)
                                        <div class="mt-3 pl-4 border-l-2" style="border-color:var(--fe-border);">
                                            <p class="text-xs font-semibold mb-1" style="color:var(--fe-text);">Supplier response</p>
                                            <p class="text-sm" style="color:var(--fe-text-muted);">{{ $review->reply->reply }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <x-frontend::common.pagination :paginator="$reviews" />
                    @endif
                </div>

                <div x-show="section === 'gallery'" x-cloak>
                    @if($supplier->gallery->isEmpty() && $supplier->videos->isEmpty())
                        <x-frontend::common.empty-state icon="fa-images" title="No gallery media yet" />
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach($supplier->gallery as $image)
                                <div class="aspect-square rounded-xl border flex items-center justify-center" style="border-color:var(--fe-border);background:var(--fe-surface-soft);">
                                    <i class="fa-solid fa-image text-2xl" style="color:var(--fe-text-subtle);"></i>
                                </div>
                            @endforeach
                            @foreach($supplier->videos as $video)
                                <a href="{{ $video->video_url }}" target="_blank" rel="noopener" class="aspect-square rounded-xl border flex items-center justify-center relative" style="border-color:var(--fe-border);background:var(--fe-surface-soft);">
                                    <i class="fa-solid fa-circle-play text-3xl" style="color:var(--fe-text-subtle);"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-frontend::marketplace.inquiry-modal
        trigger-id="supplier"
        :action="route('frontend.inquiries.supplier', $supplier->slug)"
        :context="$supplier->display_name"
    />

    @auth
        @if(request('save_intent'))
            @push('scripts')
                <script>document.getElementById('fe-save-supplier-form')?.submit();</script>
            @endpush
        @endif
    @endauth
@endsection
