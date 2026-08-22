<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.subscriptions.manage');

        $subscriptions = Subscription::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan_id', $request->integer('plan')))
            ->with(['supplierAccount.supplierProfile', 'plan'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.billing.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'status' => $request->string('status')->toString(),
            'plans' => \App\Models\SubscriptionPlan::orderBy('sort_order')->get(),
            'planId' => $request->integer('plan'),
        ]);
    }

    public function show(Subscription $subscription)
    {
        $this->authorize('platform.subscriptions.manage');

        $subscription->load(['supplierAccount.supplierProfile', 'plan', 'payments']);

        return view('backend.admin.billing.subscriptions.show', ['subscription' => $subscription]);
    }

    public function suspend(ReasonRequest $request, Subscription $subscription)
    {
        $this->authorize('platform.subscriptions.manage');

        abort_unless(in_array($subscription->status, ['active', 'trialing']), 422, 'Only an active subscription can be suspended.');

        $subscription->update(['status' => 'suspended']);

        activity('moderation')->causedBy($this->admin())->performedOn($subscription)
            ->withProperties(['reason' => $request->string('reason')])->log('Subscription suspended by admin');

        return back()->with('success', 'Subscription suspended.');
    }

    public function cancel(ReasonRequest $request, Subscription $subscription)
    {
        $this->authorize('platform.subscriptions.manage');

        $subscription->cancel($request->string('reason'));

        activity('moderation')->causedBy($this->admin())->performedOn($subscription)
            ->withProperties(['reason' => $request->string('reason')])->log('Subscription cancelled by admin');

        return back()->with('success', 'Subscription cancelled.');
    }

    public function extend(Request $request, Subscription $subscription)
    {
        $this->authorize('platform.subscriptions.manage');

        $request->validate(['days' => ['required', 'integer', 'min:1', 'max:365']]);

        $base = $subscription->expires_at ?? now();
        $newExpiry = $base->copy()->addDays($request->integer('days'));

        $subscription->update([
            'expires_at' => $newExpiry,
            'current_period_end' => $newExpiry,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($subscription)
            ->withProperties(['days' => $request->integer('days')])->log('Subscription extended by admin');

        return back()->with('success', 'Subscription extended.');
    }
}
