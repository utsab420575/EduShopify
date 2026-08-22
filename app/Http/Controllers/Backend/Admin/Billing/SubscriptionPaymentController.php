<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.subscriptions.manage');

        $payments = SubscriptionPayment::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['supplierAccount.supplierProfile', 'subscription.plan'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.billing.payments.index', [
            'payments' => $payments,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(SubscriptionPayment $payment)
    {
        $this->authorize('platform.subscriptions.manage');

        $payment->load(['supplierAccount.supplierProfile', 'subscription.plan']);

        return view('backend.admin.billing.payments.show', ['payment' => $payment]);
    }

    public function markPaid(SubscriptionPayment $payment)
    {
        $this->authorize('platform.subscriptions.manage');

        abort_unless($payment->status === 'pending', 422, 'Only a pending payment can be marked as paid.');

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($payment)->log('Payment marked paid by admin');

        return back()->with('success', 'Payment marked as paid.');
    }

    public function refund(ReasonRequest $request, SubscriptionPayment $payment)
    {
        $this->authorize('platform.subscriptions.manage');

        abort_unless($payment->status === 'paid', 422, 'Only a paid payment can be refunded.');

        $payment->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($payment)
            ->withProperties(['reason' => $request->string('reason')])->log('Payment refunded by admin');

        return back()->with('success', 'Payment refunded.');
    }
}
