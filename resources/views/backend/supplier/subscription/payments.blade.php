@extends('backend.layouts.supplier')

@section('title', 'Payment History')
@section('breadcrumb', 'Subscription & Billing / Payment History')

@section('body')

    <x-backend.page-header title="Payment History" subtitle="View records of subscription payments and invoice receipts." />

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($payments->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-receipt" title="No payment records found" description="Your subscription invoices and transaction receipts will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Payment ID</th>
                            <th class="px-3 py-3.5 font-semibold">Plan</th>
                            <th class="px-3 py-3.5 font-semibold">Amount</th>
                            <th class="px-3 py-3.5 font-semibold">Payment Method</th>
                            <th class="px-3 py-3.5 font-semibold">Date</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5 font-mono font-bold text-gray-900">
                                    {{ $payment->transaction_reference ?? 'PAY-' . $payment->id }}
                                </td>
                                <td class="px-3 py-3.5 font-medium text-gray-800">
                                    {{ $payment->subscriptionPlan?->name ?? 'Subscription' }}
                                </td>
                                <td class="px-3 py-3.5 font-bold text-gray-900">
                                    {{ $payment->currency_code }} {{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="px-3 py-3.5 text-gray-500 uppercase">
                                    {{ $payment->payment_method ?? 'Stripe' }}
                                </td>
                                <td class="px-3 py-3.5 text-gray-600">
                                    {{ $payment->paid_at?->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$payment->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
