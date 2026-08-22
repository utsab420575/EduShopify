@extends('backend.layouts.supplier')

@section('title', 'Subscription Plans')
@section('breadcrumb', 'Subscription & Billing / Available Plans')

@section('body')

    <x-backend.page-header title="Subscription Plans" subtitle="Select the right plan to scale your educational supply business on EduShopify." />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            @php $isCurrent = ($subscription ?? null) && $subscription->subscription_plan_id === $plan->id; @endphp
            <div class="bg-white rounded-2xl border {{ $isCurrent ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200' }} p-6 flex flex-col justify-between relative shadow-sm">
                @if($isCurrent)
                    <span class="absolute -top-3 right-6 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow">
                        Current Plan
                    </span>
                @endif

                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                    <div class="mt-4 mb-6">
                        <span class="text-3xl font-extrabold text-gray-900">
                            {{ $plan->is_free ? 'Free' : ($plan->currency_code . ' ' . number_format($plan->price, 0)) }}
                        </span>
                        @if(!$plan->is_free)
                            <span class="text-xs text-gray-500">/ {{ $plan->billing_type }}</span>
                        @endif
                    </div>

                    <ul class="space-y-3 text-xs text-gray-600 border-t border-gray-100 pt-4">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span>{{ $plan->max_active_listings ? $plan->max_active_listings . ' active listings' : 'Unlimited listings' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span>{{ $plan->max_monthly_quotations ? $plan->max_monthly_quotations . ' quotations/month' : 'Unlimited quotations' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span>{{ $plan->rfq_delay_minutes ? $plan->rfq_delay_minutes . ' min RFQ delay' : 'Instant RFQ access' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span>{{ $plan->max_team_members ? $plan->max_team_members . ' team member seats' : '1 team seat' }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-8 pt-4 border-t border-gray-100">
                    @if($isCurrent)
                        <button disabled class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold cursor-not-allowed">
                            Active Plan
                        </button>
                    @else
                        <a href="{{ route('supplier.subscribe', $plan->slug) }}" class="btn-primary w-full py-2.5 rounded-xl text-xs font-bold text-center block shadow-sm">
                            {{ $plan->is_free ? 'Choose Free Plan' : 'Select Plan' }}
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

@endsection
