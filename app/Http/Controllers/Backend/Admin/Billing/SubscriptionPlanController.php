<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Billing\SubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $this->authorize('platform.subscriptions.manage');

        return view('backend.admin.billing.plans.index', [
            'plans' => SubscriptionPlan::withCount('subscriptions')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.subscriptions.manage');

        return view('backend.admin.billing.plans.create', ['plan' => new SubscriptionPlan()]);
    }

    public function store(SubscriptionPlanRequest $request)
    {
        SubscriptionPlan::create($request->validated() + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.billing.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        $this->authorize('platform.subscriptions.manage');

        return view('backend.admin.billing.plans.edit', ['plan' => $plan]);
    }

    public function update(SubscriptionPlanRequest $request, SubscriptionPlan $plan)
    {
        $plan->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.billing.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $this->authorize('platform.subscriptions.manage');

        abort_if($plan->subscriptions()->exists(), 422, 'This plan has subscribers and cannot be deleted.');

        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }

    public function activate(SubscriptionPlan $plan)
    {
        $this->authorize('platform.subscriptions.manage');

        $plan->update(['is_active' => true]);

        return back()->with('success', 'Plan activated.');
    }

    public function deactivate(SubscriptionPlan $plan)
    {
        $this->authorize('platform.subscriptions.manage');

        $plan->update(['is_active' => false]);

        return back()->with('success', 'Plan deactivated.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (SubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
