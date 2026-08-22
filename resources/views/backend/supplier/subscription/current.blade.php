@extends('backend.layouts.supplier')

@section('title', 'Current Subscription')
@section('breadcrumb', 'Subscription & Billing / Current Plan')

@section('body')

    <x-backend.page-header title="Current Subscription Plan" subtitle="View your active supplier plan, limits, and billing details.">
        <x-slot:actions>
            <a href="{{ route('supplier.subscription.plans') }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Change / Upgrade Plan
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    @if(!$subscription)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center max-w-xl mx-auto mt-6">
            <i class="fa-solid fa-credit-card text-amber-600 text-3xl mb-3"></i>
            <h3 class="text-base font-bold text-gray-900">No Active Subscription</h3>
            <p class="text-xs text-gray-600 mt-1 mb-4">Choose a subscription plan to unlock listing limits and RFQ opportunity matching.</p>
            <a href="{{ route('supplier.pricing') }}" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg inline-block">
                View Available Plans
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <div class="xl:col-span-8 space-y-6">

                <x-backend.form-card title="Plan Details">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Current Tier</span>
                            <h2 class="text-xl font-bold text-gray-900 mt-0.5">{{ $plan?->name }}</h2>
                        </div>
                        <x-backend.status-badge :status="$subscription->status" />
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 text-xs">
                        <div>
                            <span class="text-gray-400 block">Price</span>
                            <span class="font-bold text-gray-800 text-sm">{{ $subscription->is_free ? 'Free' : ($plan?->currency_code . ' ' . number_format($subscription->price, 2) . ' / ' . $subscription->billing_type) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Current Period Starts</span>
                            <span class="font-semibold text-gray-800">{{ $subscription->current_period_starts_at?->format('d M Y') ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Current Period Ends</span>
                            <span class="font-semibold text-gray-800">{{ $subscription->current_period_ends_at?->format('d M Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </x-backend.form-card>

                {{-- Usage and Limits --}}
                <x-backend.form-card title="Plan Entitlements &amp; Limits">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="text-gray-500 block">Active Listings Limit</span>
                            <span class="font-bold text-gray-900 text-sm">{{ $plan?->max_active_listings ?? 'Unlimited' }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="text-gray-500 block">Monthly Quotations</span>
                            <span class="font-bold text-gray-900 text-sm">{{ $plan?->max_monthly_quotations ?? 'Unlimited' }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="text-gray-500 block">RFQ Visibility Delay</span>
                            <span class="font-bold text-gray-900 text-sm">{{ $plan?->rfq_delay_minutes ? $plan->rfq_delay_minutes . ' mins' : 'Instant' }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="text-gray-500 block">Team Members</span>
                            <span class="font-bold text-gray-900 text-sm">{{ $plan?->max_team_members ?? 1 }}</span>
                        </div>
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Quick Actions">
                    <div class="space-y-2">
                        <a href="{{ route('supplier.subscription.plans') }}" class="btn-primary text-xs font-bold w-full py-2.5 rounded-lg flex items-center justify-center gap-1.5">
                            Browse All Plans
                        </a>
                        <a href="{{ route('supplier.subscription.payments') }}" class="text-xs font-semibold w-full py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-1.5">
                            Payment History
                        </a>
                    </div>
                </x-backend.form-card>
            </div>

        </div>
    @endif

@endsection
