<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Exhibition;
use App\Models\Icon;
use App\Models\Service;
use App\Models\State;
use App\Models\SupplierType;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use InteractsWithSupplierAccount;

    /**
     * The consolidated "Business Profile" page — every sub-section (Company
     * Info, Contact, Media, Gallery & Videos, Locations & Service Areas,
     * Business Hours, Exhibitions, Documents, Services, Achievements &
     * Certifications) is an independent, expandable accordion section
     * (docs/AI/design.md §43), each saving through its own Controller/route
     * per ARCHITECTURE.md Rule 1 (no backend Livewire).
     */
    public function edit(Request $request)
    {
        $account = $this->currentAccount();
        $account->load(['supplierProfile.country', 'supplierProfile.state', 'supplierProfile.city']);
        $profile = $account->supplierProfile;

        $states = $profile?->country_id
            ? State::where('country_id', $profile->country_id)->orderBy('name')->get(['id', 'name'])
            : collect();
        $cities = $profile?->state_id
            ? City::where('state_id', $profile->state_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $serviceAreas = $account->serviceAreas()->with(['country', 'state', 'city'])->latest()->get()
            ->map(function ($area) {
                $area->area_states = $area->country_id
                    ? State::where('country_id', $area->country_id)->orderBy('name')->get(['id', 'name'])
                    : collect();
                $area->area_cities = $area->state_id
                    ? City::where('state_id', $area->state_id)->orderBy('name')->get(['id', 'name'])
                    : collect();

                return $area;
            });

        $documents = $account->supplierDocuments()->with('documentType')->latest()->get();
        $requiredDocumentTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'supplier')))
            ->get();

        $participating = Exhibition::whereHas('supplierAccounts', fn ($q) => $q->where('supplier_account_id', $account->id))
            ->with('supplierAccounts')->active()->get();
        $available = Exhibition::active()
            ->whereDoesntHave('supplierAccounts', fn ($q) => $q->where('supplier_account_id', $account->id))
            ->get();

        $businessHours = collect(range(0, 6))->map(function ($d) use ($account) {
            $existing = $account->businessHours()->whereNull('account_location_id')->where('day_of_week', $d)->first();

            return [
                'day' => $d,
                'day_name' => BusinessHour::dayName($d),
                'is_open' => $existing ? (bool) $existing->is_open : ($d >= 1 && $d <= 5),
                'open_time' => $existing?->open_time ? substr($existing->open_time, 0, 5) : '09:00',
                'close_time' => $existing?->close_time ? substr($existing->close_time, 0, 5) : '17:00',
            ];
        });

        return view('backend.supplier.company.profile', [
            'account' => $account,
            'profile' => $profile,
            'openSection' => $request->query('section'),
            'countries' => Country::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'states' => $states,
            'cities' => $cities,
            'supplierTypes' => SupplierType::where('is_active', true)->orderBy('sort_order')->get(),
            'selectedSupplierTypeIds' => $account->supplierTypes()->pluck('supplier_types.id'),
            'categoryOptions' => Category::getTreeSelectOptions(['product', 'service', 'both']),
            'selectedCategoryIds' => $account->supplierCategories()->active()->pluck('category_id'),
            'existingGallery' => $account->galleryImages()->orderBy('sort_order')->get(),
            'videos' => $account->videos()->orderBy('sort_order')->get(),
            'serviceAreas' => $serviceAreas,
            'businessHours' => $businessHours,
            'requiredDocumentTypes' => $requiredDocumentTypes,
            'documents' => $documents,
            'participating' => $participating,
            'available' => $available,
            'services' => $account->services()->with('icon.library')->orderBy('sort_order')->orderBy('id')->get(),
            'availableIcons' => Icon::active()->with('library')->orderBy('name')->get(),
            'availableAchievements' => Achievement::active()
                ->whereDoesntHave('accountAchievements', fn ($q) => $q->where('account_id', $account->id))
                ->orderBy('name')->get(),
            'myAchievementClaims' => $account->accountAchievements()->with('achievement')->latest()->get(),
            'myCertifications' => $account->certifications()->latest()->get(),
        ]);
    }
}
