@extends('backend.layouts.admin')

@section('title', $subscription->supplierAccount?->supplierProfile?->display_name)
@section('breadcrumb', 'Subscription & Billing / Subscriptions / ' . $subscription->supplierAccount?->supplierProfile?->display_name)

@section('body')

    <x-backend.page-header :title="$subscription->supplierAccount?->supplierProfile?->display_name" :subtitle="$subscription->plan?->name ?? $subscription->plan_name_snapshot">
        <x-slot:actions>
            <x-backend.status-badge :status="$subscription->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if(in_array($subscription->status, ['active', 'trialing']))
            <button @click="$dispatch('open-modal-extend')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Extend</button>
            <button @click="$dispatch('open-modal-suspend')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Suspend</button>
            <button @click="$dispatch('open-modal-cancel')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Cancel</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Subscription Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Plan</dt><dd class="font-medium text-gray-900">{{ $subscription->plan?->name ?? $subscription->plan_name_snapshot }}</dd></div>
                    <div><dt class="text-gray-500">Price</dt><dd class="font-medium text-gray-900">{{ number_format($subscription->price_snapshot ?? 0, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Started</dt><dd class="font-medium text-gray-900">{{ $subscription->starts_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Trial Ends</dt><dd class="font-medium text-gray-900">{{ $subscription->trial_ends_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Current Period End</dt><dd class="font-medium text-gray-900">{{ $subscription->current_period_end?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Auto Renew</dt><dd class="font-medium text-gray-900">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</dd></div>
                </dl>
                @if($subscription->cancellation_reason)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Cancellation Reason</p>
                        <p class="text-sm text-gray-600">{{ $subscription->cancellation_reason }}</p>
                    </div>
                @endif
            </x-backend.form-card>

            @if($subscription->payments->isNotEmpty())
                <x-backend.form-card title="Payment History">
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($subscription->payments as $payment)
                            <li class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm text-gray-800">{{ number_format($payment->amount, 2) }} {{ $payment->currency_code }}</p>
                                    <p class="text-xs text-gray-400">{{ $payment->paid_at?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-backend.status-badge :status="$payment->status" />
                                    <a href="{{ route('admin.billing.payments.show', $payment) }}" class="text-sm font-medium" style="color:var(--theme-primary)">View</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Supplier">
                <p class="text-sm font-medium text-gray-900">{{ $subscription->supplierAccount?->supplierProfile?->display_name }}</p>
                <a href="{{ route('admin.suppliers.show', $subscription->supplierAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
            </x-backend.form-card>
        </div>
    </div>

    @if(in_array($subscription->status, ['active', 'trialing']))
        <x-backend.modal id="extend" title="Extend Subscription">
            <form method="POST" action="{{ route('admin.billing.subscriptions.extend', $subscription) }}">
                @csrf
                <x-backend.input name="days" label="Extend by (days)" type="number" required min="1" max="365" />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Extend</button>
                </div>
            </form>
        </x-backend.modal>
        <x-backend.modal id="suspend" title="Suspend Subscription">
            <form method="POST" action="{{ route('admin.billing.subscriptions.suspend', $subscription) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Suspend</button>
                </div>
            </form>
        </x-backend.modal>
        <x-backend.modal id="cancel" title="Cancel Subscription">
            <form method="POST" action="{{ route('admin.billing.subscriptions.cancel', $subscription) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Cancel Subscription</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
