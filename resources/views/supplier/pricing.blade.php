<x-layouts.app>
    <div class="bg-white">

        {{-- ── Flash Messages ── --}}
        @if(session('error'))
            <div class="max-w-4xl mx-auto px-4 pt-6">
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @if(session('info'))
            <div class="max-w-4xl mx-auto px-4 pt-6">
                <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    {{ session('info') }}
                </div>
            </div>
        @endif

        {{-- ── Header ── --}}
        <div class="py-16 lg:py-20 border-b border-slate-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">Subscription Plans</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Choose the <span class="text-emerald-500">Right Plan</span> for Your Business
                </h1>
                <p class="text-slate-500 text-sm max-w-xl mx-auto">
                    Select a plan to access buyer RFQs, showcase your products, and grow your reach.
                </p>

                @if($activeSubPlan)
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-full text-emerald-700 text-xs font-semibold mt-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        Active Plan: {{ $activeSubPlan->name }}
                        &nbsp;·&nbsp;
                        Expires: {{ auth()->user()?->account?->activeSubscription?->expires_at?->format('d M Y') ?? 'Never' }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Billing Toggle ── --}}
        @php
            // Merge Free plans + Monthly/Yearly plans, sorting by sort_order
            $monthlyViewPlans = $plans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'monthly')->sortBy('sort_order')->values();
            $yearlyViewPlans  = $plans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'yearly')->sortBy('sort_order')->values();

            $hasToggle       = $monthlyPlans->isNotEmpty() && $yearlyPlans->isNotEmpty();
        @endphp
        <div
            x-data="{ billing: '{{ $monthlyPlans->isNotEmpty() ? 'monthly' : 'yearly' }}' }"
            class="py-16 lg:py-20"
        >
            {{-- Toggle --}}
            @if($hasToggle)
            <div class="flex justify-center mb-12">
                <div class="inline-flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                    <button
                        @click="billing = 'monthly'"
                        :class="billing === 'monthly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200"
                    >Monthly</button>

                    <button
                        @click="billing = 'yearly'"
                        :class="billing === 'yearly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2"
                    >
                        Yearly
                        @php
                            // Calculate average savings across all monthly/yearly plan pairs
                            $avgSaving = 0;
                            $pairCount = 0;
                            foreach ($yearlyPlans as $yp) {
                                $mp = $monthlyPlans->first();
                                if ($mp && $mp->price > 0 && $yp->price > 0) {
                                    $monthlyEquiv = $mp->price * 12;
                                    $saving = round((($monthlyEquiv - $yp->price) / $monthlyEquiv) * 100);
                                    if ($saving > 0) { $avgSaving += $saving; $pairCount++; }
                                }
                            }
                            $savingLabel = $pairCount > 0 ? round($avgSaving / $pairCount) : 0;
                        @endphp
                        @if($savingLabel > 0)
                            <span class="bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">Save {{ $savingLabel }}%</span>
                        @endif
                    </button>
                </div>
            </div>
            @endif

            {{-- ── Plan Cards ── --}}
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Monthly View (includes Free + Monthly) --}}
                @if($monthlyViewPlans->isNotEmpty())
                <div x-show="billing === 'monthly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                        @foreach($monthlyViewPlans as $plan)
                        <div class="relative bg-white rounded-2xl p-8 border {{ $plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300' }} w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">

                            @if($plan->is_featured)
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">
                                Best Value
                            </span>
                            @endif

                            @if($plan->bonus_days > 0)
                            <div class="absolute {{ $plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2' }}">
                                <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">
                                    +{{ $plan->bonus_days }} Bonus Days!
                                </span>
                            </div>
                            @endif

                            <div class="space-y-6">
                                <div>
                                    <span class="inline-flex px-2.5 py-0.5 {{ $plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }} text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                        {{ ucfirst($plan->billing_type) }}
                                    </span>
                                    <h2 class="text-xl font-bold text-slate-900 font-display">{{ $plan->name }}</h2>
                                    
                                    @if($plan->isFree())
                                        <div class="mt-3 flex items-baseline">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-500">
                                            {{ $plan->totalFreeDays() }} days free
                                            @if($plan->bonus_days > 0)<span class="text-amber-600 font-semibold"> (inc. +{{ $plan->bonus_days }}d bonus)</span>@endif
                                        </p>
                                    @else
                                        <div class="mt-3 flex items-baseline gap-1">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">{{ $plan->formattedPrice() }}</span>
                                            <span class="text-slate-500 text-sm">/mo</span>
                                        </div>
                                        @if($plan->bonus_days > 0)
                                            <p class="mt-1 text-xs text-amber-600 font-semibold">+ {{ $plan->bonus_days }} bonus days</p>
                                        @endif
                                    @endif
                                </div>

                                @include('supplier._plan-features', ['plan' => $plan])
                            </div>

                            <div class="mt-8">
                                @if($activeSubPlan?->id === $plan->id)
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                        ✓ Current Plan
                                    </div>
                                @elseif($plan->isFree() && !$isEligibleFree)
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                        Not Eligible
                                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">Already claimed or had a premium plan</span>
                                    </div>
                                @else
                                    <form action="{{ route('supplier.subscribe', $plan->slug) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                                            {{ $plan->isFree() ? 'Start Free Trial' : 'Choose ' . $plan->name }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Yearly View (includes Free + Yearly) --}}
                @if($yearlyViewPlans->isNotEmpty())
                <div x-show="billing === 'yearly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                        @foreach($yearlyViewPlans as $plan)
                        @php
                            $matchingMonthly = $monthlyPlans->first();
                            $yearlySaving = null;
                            if ($matchingMonthly && $matchingMonthly->price > 0 && $plan->price > 0) {
                                $monthlyEquiv = $matchingMonthly->price * 12;
                                $savingAmount = $monthlyEquiv - $plan->price;
                                if ($savingAmount > 0) {
                                    $yearlySaving = [
                                        'amount'  => $matchingMonthly->effectiveCurrencySymbol() . number_format($savingAmount, 0),
                                        'percent' => round(($savingAmount / $monthlyEquiv) * 100),
                                    ];
                                }
                            }
                        @endphp
                        <div class="relative bg-white rounded-2xl p-8 border {{ $plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300' }} w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">

                            @if($plan->is_featured)
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">
                                Best Value
                            </span>
                            @endif

                            @if($yearlySaving)
                            <div class="absolute top-4 right-4">
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full">
                                    Save {{ $yearlySaving['amount'] }}
                                </span>
                            </div>
                            @endif

                            @if($plan->bonus_days > 0)
                            <div class="absolute {{ $plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2' }}">
                                <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">
                                    +{{ $plan->bonus_days }} Bonus Days!
                                </span>
                            </div>
                            @endif

                            <div class="space-y-6">
                                <div>
                                    <span class="inline-flex px-2.5 py-0.5 {{ $plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700' }} text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                        {{ ucfirst($plan->billing_type) }}
                                    </span>
                                    <h2 class="text-xl font-bold text-slate-900 font-display">{{ $plan->name }}</h2>
                                    
                                    @if($plan->isFree())
                                        <div class="mt-3 flex items-baseline">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-500">
                                            {{ $plan->totalFreeDays() }} days free
                                            @if($plan->bonus_days > 0)<span class="text-amber-600 font-semibold"> (inc. +{{ $plan->bonus_days }}d bonus)</span>@endif
                                        </p>
                                    @else
                                        <div class="mt-3 flex items-baseline gap-1">
                                            <span class="text-4xl font-extrabold text-slate-950 font-display">{{ $plan->formattedPrice() }}</span>
                                            <span class="text-slate-500 text-sm">/yr</span>
                                        </div>
                                        @if($yearlySaving)
                                        <p class="mt-1 text-xs text-emerald-600 font-semibold">
                                            Save {{ $yearlySaving['amount'] }} vs monthly ({{ $yearlySaving['percent'] }}% off)
                                        </p>
                                        @endif
                                        @if($plan->bonus_days > 0)
                                            <p class="mt-1 text-xs text-amber-600 font-semibold">+ {{ $plan->bonus_days }} bonus days</p>
                                        @endif
                                    @endif
                                </div>

                                @include('supplier._plan-features', ['plan' => $plan])
                            </div>

                            <div class="mt-8">
                                @if($activeSubPlan?->id === $plan->id)
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                        ✓ Current Plan
                                    </div>
                                @elseif($plan->isFree() && !$isEligibleFree)
                                    <div class="w-full text-center px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                        Not Eligible
                                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">Already claimed or had a premium plan</span>
                                    </div>
                                @else
                                    <form action="{{ route('supplier.subscribe', $plan->slug) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                                            {{ $plan->isFree() ? 'Start Free Trial' : 'Choose ' . $plan->name }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- No plans at all --}}
                @if($plans->isEmpty())
                <div class="text-center py-20 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">No plans available right now. Please check back soon.</p>
                </div>
                @endif

            </div>

            {{-- ── FAQ strip ── --}}
            <div class="max-w-3xl mx-auto px-4 mt-16 text-center">
                <p class="text-slate-500 text-sm">
                    Have questions? <a href="mailto:support@edushopify.com" class="text-emerald-600 font-semibold hover:underline">Contact our team</a> — we're happy to help you choose the right plan.
                </p>
            </div>
        </div>
    </div>

    <x-slot:scripts>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </x-slot:scripts>
</x-layouts.app>
