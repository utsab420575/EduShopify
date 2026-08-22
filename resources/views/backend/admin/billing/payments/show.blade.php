@extends('backend.layouts.admin')

@section('title', 'Payment')
@section('breadcrumb', 'Subscription & Billing / Payments / Detail')

@section('body')

    <x-backend.page-header :title="number_format($payment->amount, 2) . ' ' . $payment->currency_code" subtitle="Subscription payment">
        <x-slot:actions>
            <x-backend.status-badge :status="$payment->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if($payment->status === 'pending')
            <form method="POST" action="{{ route('admin.billing.payments.mark-paid', $payment) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Mark as Paid</button>
            </form>
        @elseif($payment->status === 'paid')
            <button @click="$dispatch('open-modal-refund')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Refund</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Payment Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Provider</dt><dd class="font-medium text-gray-900">{{ ucfirst($payment->provider ?? '—') }}</dd></div>
                    <div><dt class="text-gray-500">Provider Payment ID</dt><dd class="font-medium text-gray-900">{{ $payment->provider_payment_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Paid At</dt><dd class="font-medium text-gray-900">{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Refunded At</dt><dd class="font-medium text-gray-900">{{ $payment->refunded_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                </dl>
                @if($payment->failure_reason)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Failure Reason</p>
                        <p class="text-sm text-gray-600">{{ $payment->failure_reason }}</p>
                    </div>
                @endif
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Supplier">
                <p class="text-sm font-medium text-gray-900">{{ $payment->supplierAccount?->supplierProfile?->display_name }}</p>
                <a href="{{ route('admin.suppliers.show', $payment->supplierAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
            </x-backend.form-card>

            @if($payment->subscription)
                <x-backend.form-card title="Subscription">
                    <p class="text-sm font-medium text-gray-900">{{ $payment->subscription->plan?->name }}</p>
                    <a href="{{ route('admin.billing.subscriptions.show', $payment->subscription) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Subscription &rarr;</a>
                </x-backend.form-card>
            @endif
        </div>
    </div>

    @if($payment->status === 'paid')
        <x-backend.modal id="refund" title="Refund Payment">
            <form method="POST" action="{{ route('admin.billing.payments.refund', $payment) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Refund</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
