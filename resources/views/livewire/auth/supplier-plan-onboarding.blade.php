<div>
    <div class="mb-6 text-center max-w-xl mx-auto">
        <span class="inline-block bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full mb-2">Capability Approved 🎉</span>
        <h1 class="text-2xl font-bold text-gray-900">Select Your Supplier Membership Plan</h1>
        <p class="text-gray-500 text-sm mt-1">Your Supplier capability is approved! Choose a plan below to activate your account and start receiving RFQ opportunities.</p>
    </div>

    @error('plan')
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm text-center">
            {{ $message }}
        </div>
    @enderror

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        @foreach($plans as $plan)
            <div class="bg-white border-2 rounded-2xl p-6 flex flex-col justify-between transition-all hover:shadow-xl {{ $plan->isFree() ? 'border-gray-200' : 'border-indigo-500 shadow-md relative' }}">
                @if(! $plan->isFree())
                    <span class="absolute -top-3 right-6 bg-indigo-600 text-white text-[10px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">Popular</span>
                @endif

                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $plan->description ?? 'Supplier membership tier.' }}</p>

                    <div class="my-4">
                        <span class="text-3xl font-extrabold text-gray-900">
                            {{ $plan->price == 0 ? 'Free' : '$' . number_format($plan->price, 2) }}
                        </span>
                        @if($plan->price > 0)
                            <span class="text-xs text-gray-500 font-medium">/ {{ $plan->type ?? 'month' }}</span>
                        @endif
                    </div>

                    <ul class="space-y-2.5 text-xs text-gray-600 mb-6 border-t border-gray-100 pt-4">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Up to <strong>{{ $plan->max_active_listings ?? 'Unlimited' }}</strong> active listings
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Up to <strong>{{ $plan->max_monthly_quotations ?? 'Unlimited' }}</strong> monthly quotations
                        </li>
                        @if($plan->has_rfq_notifications)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Real-time RFQ email alerts
                            </li>
                        @endif
                        @if($plan->has_verified_badge)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Verified Supplier Badge
                            </li>
                        @endif
                    </ul>
                </div>

                <button type="button" wire:click="selectPlan({{ $plan->id }})" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition shadow-sm {{ $plan->isFree() ? 'bg-gray-900 hover:bg-gray-800 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                    {{ $plan->isFree() ? 'Activate Free Plan' : 'Select Plan & Pay' }}
                </button>
            </div>
        @endforeach
    </div>
</div>
