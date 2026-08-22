<?php

namespace App\Livewire\Auth;

use App\Services\AccountRegistrationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Multi-step registration wizard (single URL: /register).
 *
 * Step 1 — Account Type  (individual / organization)
 * Step 2 — Capability    (buyer / supplier / both)
 * Step 3 — Details       (name, email, phone, password [+ org name])
 *
 * No DB writes until final step submit.
 */
class AccountRegister extends Component
{
    // ── Step state ──────────────────────────────────────────────────────────
    public int $step       = 1;
    public int $totalSteps = 3;

    // ── Step 1 ──────────────────────────────────────────────────────────────
    public string $account_type = '';   // individual | organization

    // ── Step 2 ──────────────────────────────────────────────────────────────
    public string $capability = '';     // buyer | supplier | both

    // ── Step 3 ──────────────────────────────────────────────────────────────
    public string $name                    = '';
    public string $email                   = '';
    public string $phone                   = '';
    public string $password                = '';
    public string $password_confirmation   = '';
    public string $organization_display_name = '';

    // ────────────────────────────────────────────────────────────────────────

    /** Step 1 → 2: validate account type selection */
    public function chooseAccountType(string $type): void
    {
        $this->account_type = $type;

        $this->validate(
            ['account_type' => 'required|in:individual,organization'],
            [],
            ['account_type' => 'account type']
        );

        $this->step = 2;
    }

    /** Step 2 → 3: validate capability selection */
    public function chooseCapability(string $cap): void
    {
        $this->capability = $cap;
        $this->validate(
            ['capability' => 'required|in:buyer,supplier,both'],
            [],
            ['capability' => 'capability']
        );
        $this->step = 3;
    }

    /** Go back one step */
    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->resetErrorBag();
        }
    }

    /**
     * Final submission — validate and call AccountRegistrationService.
     * No DB work happens before this point.
     */
    public function register(AccountRegistrationService $service): void
    {
        $rules = [
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:individual,organization',
            'capability'   => 'required|in:buyer,supplier,both',
        ];

        if ($this->account_type === 'organization') {
            $rules['organization_display_name'] = 'required|string|max:200';
        }

        $this->validate($rules, [], [
            'name'                     => 'full name',
            'email'                    => 'email address',
            'password'                 => 'password',
            'organization_display_name' => 'organisation name',
        ]);

        $user = $service->register([
            'name'                     => $this->name,
            'email'                    => $this->email,
            'phone'                    => $this->phone,
            'password'                 => $this->password,
            'account_type'             => $this->account_type,
            'capability'               => $this->capability,
            'organization_display_name' => $this->organization_display_name,
        ]);

        Auth::login($user);

        $this->redirect(route('verification.notice'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.account-register')
            ->layout('components.layouts.auth', [
                'title'    => 'Create Account',
                'maxWidth' => 'max-w-lg',
            ]);
    }
}
