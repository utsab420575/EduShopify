<?php

namespace App\Livewire\Buyer;

use App\Services\CapabilityApplicationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Review screen — shows read-only profile summary before submission.
 * Requires profile_completed_at to be set; redirects back if not.
 */
class BuyerApplicationReview extends Component
{
    public function mount(): void
    {
        $user    = Auth::user();
        $account = $user->account;

        if (! $account || ! $account->buyerCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        $cap = $account->buyerCapability;

        // Only draft status should reach review
        if (! in_array($cap->status, ['draft'])) {
            $this->redirect(route('buyer.onboarding.profile'), navigate: false);
            return;
        }

        $profile = $account->buyerProfile;
        if (! $profile || ! $profile->isComplete()) {
            $this->redirect(route('buyer.onboarding.profile'), navigate: false);
        }
    }

    public function submit(CapabilityApplicationService $service): void
    {
        $user    = Auth::user();
        $account = $user->account;

        try {
            $service->submit($account, 'buyer', $user);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->redirect(route('buyer.dashboard'), navigate: false);
    }

    public function render()
    {
        $user    = Auth::user();
        $account = $user->account;
        $profile = $account?->buyerProfile;
        $cap     = $account?->buyerCapability;

        $profile?->load(['buyerType', 'country', 'state', 'city']);

        return view('livewire.buyer.buyer-application-review', compact('account', 'profile', 'cap'))
            ->layout('components.layouts.auth', [
                'title'    => 'Review Your Application',
                'maxWidth' => 'max-w-2xl',
            ]);
    }
}
