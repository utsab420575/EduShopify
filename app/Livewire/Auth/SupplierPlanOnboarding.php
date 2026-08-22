<?php

namespace App\Livewire\Auth;

use App\Models\SubscriptionPlan;
use App\Services\SubscriptionSelectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SupplierPlanOnboarding extends Component
{
    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->hasActiveCapability('supplier')) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // If supplier already has active subscription, redirect to dashboard
        if ($account->subscriptions()->whereIn('status', ['active', 'trialing'])->exists()) {
            $this->redirect(route('supplier.dashboard'), navigate: false);
        }
    }

    public function selectPlan(int $planId, SubscriptionSelectionService $service): void
    {
        $user    = Auth::user();
        $account = $user->account;
        $plan    = SubscriptionPlan::findOrFail($planId);

        try {
            $sub = $service->select($account, $plan, $user);

            if ($sub->status === 'active') {
                session()->flash('success', "Subscription plan '{$plan->name}' activated successfully!");
                $this->redirect(route('supplier.dashboard'), navigate: false);
            } else {
                session()->flash('info', 'Subscription pending payment activation.');
                $this->redirect(route('supplier.onboarding.subscription-pending'), navigate: false);
            }
        } catch (\Throwable $e) {
            $this->addError('plan', $e->getMessage());
        }
    }

    public function render()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('livewire.auth.supplier-plan-onboarding', compact('plans'))
            ->layout('components.layouts.auth', [
                'title'    => 'Select Subscription Plan',
                'maxWidth' => 'max-w-4xl',
            ]);
    }
}
