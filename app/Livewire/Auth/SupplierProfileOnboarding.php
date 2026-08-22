<?php

namespace App\Livewire\Auth;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\SupplierType;
use App\Services\SupplierOnboardingStateService;
use App\Services\SupplierProfileService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupplierProfileOnboarding extends Component
{
    use WithFileUploads;

    public $logo          = null;
    public $banner        = null;
    public $profile_photo = null;

    public string $display_name      = '';
    public string $legal_name        = '';
    public string $legal_entity_type = ''; // Sole Proprietorship, Partnership, Limited Company, Corporation, Government Entity
    public array  $supplier_type_ids = [];

    public string $contact_person = '';
    public string $contact_email  = '';
    public string $contact_phone  = '';
    public string $whatsapp       = '';
    public string $support_email  = '';

    public string $website       = '';
    public ?int   $founded_year  = null;
    public ?int   $employees     = null;

    public ?int   $country_id    = null;
    public ?int   $state_id      = null;
    public ?int   $city_id       = null;
    public string $address       = '';
    public string $description   = '';

    public array $states = [];
    public array $cities = [];

    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->supplierCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // Pre-fill supplier types
        $this->supplier_type_ids = $account->supplierTypes()->pluck('supplier_types.id')->toArray();

        $profile = $account->supplierProfile;
        if ($profile) {
            $this->display_name      = $profile->display_name      ?? '';
            $this->legal_name        = $profile->legal_name        ?? '';
            $this->legal_entity_type = $profile->legal_entity_type ?? ($profile->company_type ?? '');
            $this->contact_person    = $profile->contact_person    ?? '';
            $this->contact_email     = $profile->contact_email     ?? '';
            $this->contact_phone     = $profile->contact_phone     ?? '';
            $this->whatsapp          = $profile->whatsapp          ?? '';
            $this->support_email     = $profile->support_email     ?? '';
            $this->website           = $profile->website           ?? '';
            $this->founded_year      = $profile->founded_year;
            $this->employees         = $profile->employees;
            $this->country_id        = $profile->country_id;
            $this->state_id          = $profile->state_id;
            $this->city_id           = $profile->city_id;
            $this->address           = $profile->address           ?? '';
            $this->description       = $profile->description       ?? '';

            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])->toArray();
                $this->cities = $this->state_id
                    ? City::where('state_id', $this->state_id)->orderBy('name')->get(['id', 'name'])->toArray()
                    : City::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])->toArray();
            }
        }
    }

    public function updatedCountryId($value): void
    {
        $this->state_id = null;
        $this->city_id  = null;
        $this->states   = $value ? State::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->cities   = $value ? City::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
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

    public function selectAllSupplierTypes(): void
    {
        $this->supplier_type_ids = SupplierType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    public function clearAllSupplierTypes(): void
    {
        $this->supplier_type_ids = [];
    }

    public function toggleAllSupplierTypes(): void
    {
        $allIds = SupplierType::where('is_active', true)->orderBy('sort_order')->pluck('id')->map(fn($id) => (int) $id)->toArray();
        $current = array_values(array_filter(array_map('intval', (array) $this->supplier_type_ids)));

        if (count($current) >= count($allIds) && count($allIds) > 0) {
            $this->clearAllSupplierTypes();
        } else {
            $this->selectAllSupplierTypes();
        }
    }

    public function toggleSupplierType(int $id): void
    {
        $current = array_values(array_filter(array_map('intval', (array) $this->supplier_type_ids)));
        if (in_array($id, $current, true)) {
            $this->supplier_type_ids = array_values(array_diff($current, [$id]));
        } else {
            $this->supplier_type_ids = array_values(array_unique(array_merge($current, [$id])));
        }
    }

    public function saveDraft(SupplierProfileService $service): void
    {
        $account = Auth::user()->account;
        $service->saveDraft($account, $this->profileData());
        session()->flash('success', 'Draft saved.');
    }

    public function completeAndContinue(SupplierProfileService $service): void
    {
        $account = Auth::user()->account;

        try {
            $service->completeProfile($account, $this->profileData());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->redirect(route('supplier.onboarding.documents'), navigate: false);
    }

    private function profileData(): array
    {
        return [
            'logo'              => $this->logo,
            'banner'            => $this->banner,
            'profile_photo'     => $this->profile_photo,
            'display_name'      => $this->display_name,
            'legal_name'        => $this->legal_name,
            'legal_entity_type' => $this->legal_entity_type,
            'supplier_type_ids' => $this->supplier_type_ids,
            'contact_person'    => $this->contact_person,
            'contact_email'     => $this->contact_email,
            'contact_phone'     => $this->contact_phone,
            'whatsapp'          => $this->whatsapp,
            'support_email'     => $this->support_email,
            'website'           => $this->website,
            'founded_year'      => $this->founded_year,
            'employees'         => $this->employees,
            'country_id'        => $this->country_id,
            'state_id'          => $this->state_id,
            'city_id'           => $this->city_id,
            'address'           => $this->address,
            'description'       => $this->description,
        ];
    }

    public function render()
    {
        return view('livewire.auth.supplier-profile-onboarding', [
            'supplierTypes'     => SupplierType::where('is_active', true)->orderBy('sort_order')->get(),
            'countries'         => Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'flag']),
            'legalEntityTypes'  => ['Sole Proprietorship', 'Partnership', 'Limited Company', 'Corporation', 'Government Entity'],
            'account'           => Auth::user()->account,
        ])->layout('components.layouts.auth', [
            'title'    => 'Supplier Profile Onboarding',
            'maxWidth' => 'max-w-3xl',
        ]);
    }
}
