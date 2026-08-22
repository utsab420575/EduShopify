<?php

namespace App\Livewire\Buyer;

use App\Models\BuyerType;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Services\BuyerOnboardingStateService;
use App\Services\BuyerProfileService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class BuyerProfileOnboarding extends Component
{
    use WithFileUploads;

    // ── Profile fields ───────────────────────────────────────────────────────
    public $logo                 = null;
    public array  $buyer_type_ids    = [];
    public string $display_name        = '';
    public string $organization_name   = '';
    public string $contact_person      = '';
    public string $position            = '';
    public string $email               = '';
    public string $phone               = '';
    public string $website             = '';
    public ?int   $country_id          = null;
    public ?int   $state_id            = null;
    public ?int   $city_id             = null;
    public string $address             = '';
    public string $tax_id              = '';
    public string $bio                 = '';
    public string $procurement_info    = '';

    // ── Location cascades ────────────────────────────────────────────────────
    public array $states = [];
    public array $cities = [];

    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // Verify buyer capability exists
        if (! $account->buyerCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // Pre-fill buyer types from pivot table or fallback to single buyer_type_id
        $this->buyer_type_ids = $account->buyerTypes()->pluck('buyer_types.id')->toArray();

        // Pre-fill from existing profile skeleton
        $profile = $account->buyerProfile;
        if ($profile) {
            if (empty($this->buyer_type_ids) && $profile->buyer_type_id) {
                $this->buyer_type_ids = [(int) $profile->buyer_type_id];
            }
            $this->display_name      = $profile->display_name      ?? '';
            $this->organization_name = $profile->organization_name ?? '';
            $this->contact_person    = $profile->contact_person    ?? '';
            $this->position          = $profile->position          ?? '';
            $this->email             = $profile->email             ?? '';
            $this->phone             = $profile->phone             ?? '';
            $this->website           = $profile->website           ?? '';
            $this->country_id        = $profile->country_id;
            $this->state_id          = $profile->state_id;
            $this->city_id           = $profile->city_id;
            $this->address           = $profile->address           ?? '';
            $this->tax_id            = $profile->tax_id            ?? '';
            $this->bio               = $profile->bio               ?? '';
            $this->procurement_info  = $profile->procurement_info  ?? '';

            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)
                    ->orderBy('name')->get(['id', 'name'])->toArray();
                $this->cities = $this->state_id
                    ? City::where('state_id', $this->state_id)->orderBy('name')->get(['id', 'name'])->toArray()
                    : City::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])->toArray();
            }
        }

        // Auto-prepend https:// if missing
        if ($this->website && ! str_starts_with($this->website, 'http')) {
            $this->website = 'https://' . $this->website;
        }
    }

    public function updatedCountryId($value): void
    {
        $this->state_id = null;
        $this->city_id  = null;
        $this->states   = $value
            ? State::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray()
            : [];
        $this->cities   = $value
            ? City::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray()
            : [];
    }

    public function updatedStateId($value): void
    {
        $this->city_id = null;
        if ($value) {
            $this->cities = City::where('state_id', $value)->orderBy('name')->get(['id', 'name'])->toArray();
        } elseif ($this->country_id) {
            $this->cities = City::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])->toArray();
        } else {
            $this->cities = [];
        }
    }

    public function updatedWebsite($value): void
    {
        if ($value && ! str_starts_with($value, 'http')) {
            $this->website = 'https://' . $value;
        }
    }

    /** Save draft — no completion required */
    public function saveDraft(BuyerProfileService $service): void
    {
        $account = Auth::user()->account;
        $service->saveDraft($account, $this->profileData());
        session()->flash('success', 'Draft saved.');
    }

    /** Validate required fields → complete → redirect to review */
    public function completeAndContinue(BuyerProfileService $service): void
    {
        $account = Auth::user()->account;

        try {
            $service->completeProfile($account, $this->profileData());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->redirect(route('buyer.onboarding.review'), navigate: false);
    }

    public function selectAllBuyerTypes(): void
    {
        $this->buyer_type_ids = BuyerType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    public function clearAllBuyerTypes(): void
    {
        $this->buyer_type_ids = [];
    }

    public function toggleAllBuyerTypes(): void
    {
        $allIds = BuyerType::where('is_active', true)->orderBy('sort_order')->pluck('id')->map(fn($id) => (int) $id)->toArray();
        $current = array_values(array_filter(array_map('intval', (array) $this->buyer_type_ids)));

        if (count($current) >= count($allIds) && count($allIds) > 0) {
            $this->clearAllBuyerTypes();
        } else {
            $this->selectAllBuyerTypes();
        }
    }

    public function toggleBuyerType(int $id): void
    {
        $current = array_values(array_filter(array_map('intval', (array) $this->buyer_type_ids)));
        if (in_array($id, $current, true)) {
            $this->buyer_type_ids = array_values(array_diff($current, [$id]));
        } else {
            $this->buyer_type_ids = array_values(array_unique(array_merge($current, [$id])));
        }
    }

    private function profileData(): array
    {
        return [
            'logo'              => $this->logo,
            'display_name'      => $this->display_name,
            'organization_name' => $this->organization_name,
            'buyer_type_ids'    => $this->buyer_type_ids,
            'buyer_type_id'     => $this->buyer_type_ids[0] ?? null,
            'contact_person'    => $this->contact_person,
            'position'          => $this->position,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'website'           => $this->website,
            'country_id'        => $this->country_id,
            'state_id'          => $this->state_id,
            'city_id'           => $this->city_id,
            'address'           => $this->address,
            'tax_id'            => $this->tax_id,
            'bio'               => $this->bio,
            'procurement_info'  => $this->procurement_info,
        ];
    }

    public function safeTemporaryUrl($file): ?string
    {
        if (! $file) return null;
        try {
            return is_object($file) && method_exists($file, 'temporaryUrl')
                ? $file->temporaryUrl()
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.buyer.buyer-profile-onboarding', [
            'buyerTypes' => BuyerType::where('is_active', true)->orderBy('sort_order')->get(),
            'countries'  => Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'flag']),
            'account'    => Auth::user()->account,
        ])->layout('components.layouts.auth', [
            'title'    => 'Complete Your Profile',
            'maxWidth' => 'max-w-3xl',
        ]);
    }
}
