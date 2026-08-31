<?php

namespace App\Livewire\Auth;

use App\Models\Account;
use App\Models\AccountLocation;
use App\Models\BusinessHour;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Exhibition;
use App\Models\State;
use App\Models\SocialLink;
use App\Models\SocialPlatform;
use App\Models\SubscriptionPlan;
use App\Models\SupplierDocument;
use App\Models\SupplierGallery;
use App\Models\SupplierType;
use App\Models\SupplierVideo;
use App\Services\CapabilityApplicationService;
use App\Services\SubscriptionSelectionService;
use App\Services\SupplierDocumentService;
use App\Services\SupplierProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The complete Supplier Application wizard — one Livewire component covering
 * everything from company details through plan selection. Replaces the
 * previous 3-page split (SupplierProfileOnboarding → SupplierDocumentOnboarding
 * → SupplierApplicationReview, with SupplierPlanOnboarding reachable only
 * after Admin approval). Submission for Admin review now happens right after
 * plan selection, at the end of this same flow.
 */
class SupplierApplication extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 7;
    public int $maxStepReached = 1;

    /* ── Step 1: Company Information ── */
    public string $display_name = '';
    public string $legal_name = '';
    public string $legal_entity_type = '';
    public string $website = '';
    public ?int $founded_year = null;
    public ?int $employees = null;
    public array $locations = [];

    /* ── Step 2: Branding & Media ── */
    public $logo;
    public $banner;
    public $profile_photo;
    public array $gallery_files = [];
    public array $video_urls = [];

    /* ── Step 3: Supplier Types & Exhibitions ── */
    public array $supplier_type_ids = [];
    public array $exhibition_ids = [];

    /* ── Step 4: Contact Information & Social Media ── */
    public string $contact_person = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $whatsapp = '';
    public string $support_email = '';
    /** Keyed by social_platform_id => ['url' => string]. */
    public array $social_links = [];

    /* ── Step 5: Verification Documents ── */
    public string $new_document_type_id = '';
    public $new_file;
    public string $new_expiry = '';
    public string $new_custom_name = '';

    /* ── Step 6: Business Hours ── */
    public array $business_hours = [];
    public string $default_open_time = '09:00';
    public string $default_close_time = '17:00';

    /* ── Step 7: Choose Your Plan ── */
    public ?int $selectedPlanId = null;

    /* ─────────────────── Lifecycle ─────────────────── */

    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->supplierCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        $capabilityStatus = $account->capabilityStatus('supplier');

        // Once submitted, the wizard is closed until Admin asks for a revision.
        if (! in_array($capabilityStatus, ['draft', 'revision_required'], true)) {
            $this->redirect(route('supplier.pending'), navigate: false);
            return;
        }

        $this->supplier_type_ids = $account->supplierTypes()->pluck('supplier_types.id')->toArray();
        $this->exhibition_ids = $account->exhibitions()->pluck('exhibitions.id')->toArray();

        $this->locations = $account->locations->map(fn (AccountLocation $loc) => [
            'id' => $loc->id,
            'country_id' => $loc->country_id,
            'state_id' => $loc->state_id,
            'city_id' => $loc->city_id,
            'address' => trim(($loc->address_line_1 ?? '').($loc->address_line_2 ? ', '.$loc->address_line_2 : '')),
            'states' => $loc->country_id ? State::where('country_id', $loc->country_id)->orderBy('name')->get(['id', 'name'])->toArray() : [],
            'cities' => $loc->state_id ? City::where('state_id', $loc->state_id)->orderBy('name')->get(['id', 'name'])->toArray() : [],
        ])->toArray();
        if (empty($this->locations)) {
            $this->locations = [$this->emptyLocationRow()];
        }

        $this->business_hours = collect(range(0, 6))->map(function ($d) use ($account) {
            $existing = $account->supplierProfile
                ? BusinessHour::where('supplier_account_id', $account->id)->whereNull('account_location_id')->where('day_of_week', $d)->first()
                : null;

            return [
                'day' => $d,
                'day_name' => BusinessHour::dayName($d),
                'is_open' => $existing ? (bool) $existing->is_open : ($d >= 1 && $d <= 5),
                'open_time' => $existing?->open_time ? substr($existing->open_time, 0, 5) : '09:00',
                'close_time' => $existing?->close_time ? substr($existing->close_time, 0, 5) : '17:00',
            ];
        })->toArray();

        $profile = $account->supplierProfile;
        if ($profile) {
            $this->display_name = $profile->display_name ?? '';
            $this->legal_name = $profile->legal_name ?? '';
            $this->legal_entity_type = $profile->legal_entity_type ?? ($profile->company_type ?? '');
            $this->website = $profile->website ?? '';
            $this->founded_year = $profile->founded_year;
            $this->employees = $profile->employees;
            $this->contact_person = $profile->contact_person ?? '';
            $this->contact_email = $profile->contact_email ?? '';
            $this->contact_phone = $profile->contact_phone ?? '';
            $this->whatsapp = $profile->whatsapp ?? '';
            $this->support_email = $profile->support_email ?? '';
        }

        foreach (SocialPlatform::active()->get() as $platform) {
            $this->social_links[$platform->id] = ['url' => ''];
        }
        foreach ($account->socialLinks as $link) {
            $this->social_links[$link->social_platform_id] = ['url' => $link->url];
        }

        // Resume exactly where they left off, including across a full
        // logout — both the furthest step unlocked and the exact step they
        // were sitting on are persisted on the profile row (see
        // persistStepProgress()).
        if ($profile && $profile->max_step_reached !== null) {
            $this->maxStepReached = min($profile->max_step_reached, $this->totalSteps);
            $this->step = max(1, min($profile->current_step ?? $this->maxStepReached, $this->totalSteps));
        } elseif ($profile && $profile->isComplete()) {
            // No step tracking recorded (this profile finished the old
            // 3-page flow before per-step tracking existed), but the
            // profile itself is complete — steps 1-4 are done, so resume
            // at Documents rather than sending them back to step 1.
            $this->maxStepReached = 5;
            $this->step = 5;
        }
        // Otherwise: a brand-new skeleton profile row (created automatically
        // at registration, before any wizard step has actually been saved)
        // — the class defaults of step=1/maxStepReached=1 already apply.

        // A revision request re-opens every step for editing regardless of
        // how far the wizard had previously progressed.
        if ($capabilityStatus === 'revision_required') {
            $this->maxStepReached = $this->totalSteps;
        }
    }

    /**
     * Persists exactly where the applicant is (current_step) and how far
     * they've unlocked (max_step_reached) so a logout mid-application
     * resumes at the same step next login, instead of restarting at 1.
     * No-op before step 1 has ever been saved (no profile row to attach to).
     */
    protected function persistStepProgress(): void
    {
        $profile = $this->account()->supplierProfile;

        if (! $profile) {
            return;
        }

        $profile->update([
            'current_step' => $this->step,
            'max_step_reached' => max($this->maxStepReached, $profile->max_step_reached ?? 1),
        ]);
    }

    protected function emptyLocationRow(): array
    {
        return ['id' => null, 'country_id' => null, 'state_id' => null, 'city_id' => null, 'address' => '', 'states' => [], 'cities' => []];
    }

    protected function account(): Account
    {
        return Auth::user()->account;
    }

    /* ─────────────────── Locations (repeatable) ─────────────────── */

    public function addLocation(): void
    {
        $this->locations[] = $this->emptyLocationRow();
    }

    public function removeLocation(int $index): void
    {
        if (count($this->locations) <= 1) {
            return;
        }
        array_splice($this->locations, $index, 1);
        $this->locations = array_values($this->locations);
    }

    /**
     * Livewire has no magic updatedXxx() hook for a dynamic array path like
     * locations.3.country_id, so this catch-all repopulates the right row's
     * state/city options whenever any location's country or state changes.
     */
    public function updated($name, $value): void
    {
        if (Str::is('locations.*.country_id', $name)) {
            $index = (int) explode('.', $name)[1];
            $this->locations[$index]['states'] = $value ? State::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->locations[$index]['cities'] = [];
            $this->locations[$index]['state_id'] = null;
            $this->locations[$index]['city_id'] = null;
        }

        if (Str::is('locations.*.state_id', $name)) {
            $index = (int) explode('.', $name)[1];
            $this->locations[$index]['cities'] = $value ? City::where('state_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->locations[$index]['city_id'] = null;
        }
    }

    /* ─────────────────── Gallery ─────────────────── */

    /**
     * Removes a pending (not-yet-saved) gallery upload before it's ever
     * written to the database — just drops it from the temp array.
     */
    public function removeGalleryFile(int $index): void
    {
        array_splice($this->gallery_files, $index, 1);
        $this->gallery_files = array_values($this->gallery_files);
    }

    /**
     * Deletes an already-saved gallery image (from a previous visit) — both
     * the database row and the underlying file on disk.
     */
    public function removeExistingGalleryImage(int $id): void
    {
        $image = SupplierGallery::where('supplier_account_id', $this->account()->id)->find($id);

        if (! $image) {
            return;
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }

    /* ─────────────────── Videos ─────────────────── */

    public function addVideoUrl(): void
    {
        if (count($this->video_urls) < 5) {
            $this->video_urls[] = '';
        }
    }

    public function removeVideoUrl(int $index): void
    {
        array_splice($this->video_urls, $index, 1);
        $this->video_urls = array_values($this->video_urls);
    }

    /**
     * Deletes an already-saved video (from a previous visit).
     */
    public function removeExistingVideo(int $id): void
    {
        SupplierVideo::where('supplier_account_id', $this->account()->id)->where('id', $id)->delete();
    }

    /* ─────────────────── Supplier Types ─────────────────── */

    public function toggleSupplierType(int $id): void
    {
        $current = array_values(array_filter(array_map('intval', (array) $this->supplier_type_ids)));
        if (in_array($id, $current, true)) {
            $this->supplier_type_ids = array_values(array_diff($current, [$id]));
        } else {
            $this->supplier_type_ids = array_values(array_unique(array_merge($current, [$id])));
        }
    }

    public function selectAllSupplierTypes(): void
    {
        $this->supplier_type_ids = SupplierType::where('is_active', true)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    public function clearAllSupplierTypes(): void
    {
        $this->supplier_type_ids = [];
    }

    public function toggleAllSupplierTypes(): void
    {
        $allIds = SupplierType::where('is_active', true)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $current = array_values(array_filter(array_map('intval', (array) $this->supplier_type_ids)));

        if (count($current) >= count($allIds) && count($allIds) > 0) {
            $this->clearAllSupplierTypes();
        } else {
            $this->selectAllSupplierTypes();
        }
    }

    /* ─────────────────── Documents ─────────────────── */

    public function openAddDocument(?int $documentTypeId = null): void
    {
        $this->resetValidation();
        $this->reset(['new_document_type_id', 'new_file', 'new_expiry', 'new_custom_name']);
        $this->new_document_type_id = $documentTypeId ? (string) $documentTypeId : '';
        $this->dispatch('open-add-document');
    }

    public function addDocument(SupplierDocumentService $service): void
    {
        $isOther = $this->new_document_type_id === 'other';

        $rules = [
            'new_document_type_id' => 'required|string',
            'new_file' => 'required|file|max:10240',
            'new_expiry' => 'nullable|date',
        ];
        if ($isOther) {
            $rules['new_custom_name'] = 'required|string|max:200';
        }
        $this->validate($rules);

        $expiry = ! empty($this->new_expiry) ? new \DateTime($this->new_expiry) : null;

        try {
            $account = $this->account();
            $documentTypeId = $isOther ? null : (int) $this->new_document_type_id;
            $customName = $isOther ? $this->new_custom_name : null;

            $service->upload($account, $documentTypeId, $this->new_file, Auth::user(), $expiry, $customName);

            $this->reset(['new_document_type_id', 'new_file', 'new_expiry', 'new_custom_name']);
            $this->dispatch('document-uploaded');
        } catch (\Throwable $e) {
            $this->addError('new_file', $e->getMessage());
        }
    }

    /* ─────────────────── Business Hours ─────────────────── */

    public function applyDefaultHours(): void
    {
        foreach ($this->business_hours as $index => $bh) {
            if ($bh['is_open']) {
                $this->business_hours[$index]['open_time'] = $this->default_open_time;
                $this->business_hours[$index]['close_time'] = $this->default_close_time;
            }
        }
    }

    /* ─────────────────── Step Navigation ─────────────────── */

    public function goToStep(int $target): void
    {
        if ($target >= 1 && $target <= $this->maxStepReached) {
            $this->step = $target;
            $this->persistStepProgress();
        }
    }

    public function nextStep(SupplierProfileService $profileService): void
    {
        $rules = $this->rulesForStep($this->step);
        if (! empty($rules)) {
            $this->validate($rules, $this->messagesForStep($this->step));
        }

        if (in_array($this->step, [1, 2, 3], true)) {
            $profileService->saveDraft($this->account(), $this->profileData());
        }

        if ($this->step === 4) {
            // completeProfile() calls saveDraft() internally — everything
            // assertComplete() needs is collected by now.
            $profileService->completeProfile($this->account(), $this->profileData());
        }

        if ($this->step === 5) {
            $this->assertRequiredDocumentsPresent();
        }

        if ($this->step === 6) {
            $this->saveBusinessHours();
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;
            $this->maxStepReached = max($this->maxStepReached, $this->step);
        }

        $this->persistStepProgress();
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->persistStepProgress();
        }
    }

    public function saveDraft(SupplierProfileService $profileService): void
    {
        $profileService->saveDraft($this->account(), $this->profileData());
        session()->flash('success', 'Draft saved.');
    }

    protected function assertRequiredDocumentsPresent(): void
    {
        $account = $this->account();

        $requiredTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'supplier'))->where('is_required', true))
            ->get(['id', 'name']);

        $missing = [];
        foreach ($requiredTypes as $docType) {
            $hasCurrent = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $docType->id)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrent) {
                $missing[] = $docType->name;
            }
        }

        if (count($missing) > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'documents' => 'Please upload all required documents: '.implode(', ', $missing),
            ]);
        }
    }

    protected function saveBusinessHours(): void
    {
        $account = $this->account();

        foreach ($this->business_hours as $bh) {
            BusinessHour::updateOrCreate(
                ['supplier_account_id' => $account->id, 'account_location_id' => null, 'day_of_week' => $bh['day']],
                [
                    'is_open' => (bool) $bh['is_open'],
                    'open_time' => $bh['is_open'] ? $bh['open_time'] : null,
                    'close_time' => $bh['is_open'] ? $bh['close_time'] : null,
                ]
            );
        }
    }

    /* ─────────────────── Validation per step ─────────────────── */

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'display_name' => 'required|string|max:200',
                'legal_name' => 'required|string|max:200',
                'legal_entity_type' => 'nullable|string|max:100',
                'website' => 'nullable|max:255|url',
                'founded_year' => 'nullable|integer|min:1800|max:'.date('Y'),
                'employees' => 'nullable|integer|min:1',
                'locations' => 'required|array|min:1',
                'locations.*.country_id' => 'required|exists:countries,id',
                'locations.*.state_id' => 'required|exists:states,id',
                'locations.*.city_id' => 'required|exists:cities,id',
                'locations.*.address' => 'required|string|max:500',
            ],
            2 => [
                // Already having a saved logo (from a previous visit) satisfies
                // this — only a first-time pass with nothing uploaded yet and
                // nothing on file is blocked, so returning applicants aren't
                // forced to re-upload every time they revisit this step.
                'logo' => $this->account()->supplierProfile?->logo ? 'nullable|image|max:5120' : 'required|image|max:5120',
                'banner' => 'nullable|image|max:5120',
                'profile_photo' => 'nullable|image|max:5120',
                'gallery_files' => 'nullable|array|max:10',
                'gallery_files.*' => 'image|max:5120',
                'video_urls' => 'nullable|array|max:5',
                'video_urls.*' => 'nullable|url',
            ],
            3 => [
                'supplier_type_ids' => 'required|array|min:1',
                'supplier_type_ids.*' => 'exists:supplier_types,id',
                'exhibition_ids' => 'nullable|array',
                'exhibition_ids.*' => 'exists:exhibitions,id',
            ],
            4 => [
                'contact_person' => 'required|string|max:150',
                'contact_phone' => 'required|string|max:30',
                'contact_email' => 'required|email|max:150',
                'whatsapp' => 'nullable|string|max:30',
                'support_email' => 'nullable|email|max:150',
                'social_links.*.url' => 'nullable|url|max:500',
            ],
            default => [],
        };
    }

    /**
     * Custom validation messages, keyed by exact field path rather than
     * wildcard — built against the current location count so each row gets
     * its own plain-language message (e.g. "for Location 2") instead of
     * Laravel's default "The locations.0.country_id field is required."
     */
    protected function messagesForStep(int $step): array
    {
        if ($step === 2) {
            return ['logo.required' => 'Please upload a company logo before continuing.'];
        }

        if ($step !== 1) {
            return [];
        }

        $messages = [
            'display_name.required' => 'Please enter your company display name.',
            'legal_name.required' => 'Please enter your registered legal name.',
            'website.url' => 'Please enter a valid website address (e.g. https://example.com).',
        ];

        foreach ($this->locations as $i => $loc) {
            $label = $i === 0 ? 'your primary location' : 'Location '.($i + 1);
            $messages["locations.$i.country_id.required"] = "Please select a country for $label.";
            $messages["locations.$i.state_id.required"] = "Please select a state/region for $label.";
            $messages["locations.$i.city_id.required"] = "Please select a city for $label.";
            $messages["locations.$i.address.required"] = "Please enter an address for $label.";
        }

        return $messages;
    }

    protected function profileData(): array
    {
        $primaryLocation = $this->locations[0] ?? null;

        $data = [
            'logo' => $this->logo,
            'banner' => $this->banner,
            'profile_photo' => $this->profile_photo,
            'display_name' => $this->display_name,
            'legal_name' => $this->legal_name,
            'legal_entity_type' => $this->legal_entity_type,
            'supplier_type_ids' => $this->supplier_type_ids,
            'contact_person' => $this->contact_person,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'whatsapp' => $this->whatsapp,
            'support_email' => $this->support_email,
            'website' => $this->website,
            'founded_year' => $this->founded_year,
            'employees' => $this->employees,
            'country_id' => $primaryLocation['country_id'] ?? null,
            'state_id' => $primaryLocation['state_id'] ?? null,
            'city_id' => $primaryLocation['city_id'] ?? null,
            'address' => $primaryLocation['address'] ?? null,
        ];

        $account = $this->account();
        $this->saveLocations($account);
        $this->saveGalleryAndVideos($account);
        $this->saveSocialLinks($account);
        $account->supplierTypes()->sync(collect($this->supplier_type_ids)->mapWithKeys(fn ($id, $i) => [$id => ['is_primary' => $i === 0]]));
        $account->exhibitions()->sync($this->exhibition_ids);

        return $data;
    }

    /**
     * Saves each platform's URL as its own social_links row (one per
     * platform per account). Clearing a field that previously had a saved
     * link removes that row rather than leaving a stale empty one behind.
     */
    protected function saveSocialLinks(Account $account): void
    {
        // Queried through the relation (not a raw socialable_type string) so
        // it resolves correctly under the app's registered morph map — see
        // AppServiceProvider::registerMorphMap(), which stores Account rows
        // under the 'account' alias rather than the literal class name.
        foreach ($this->social_links as $platformId => $row) {
            $url = trim($row['url'] ?? '');

            if ($url === '') {
                $account->socialLinks()->where('social_platform_id', $platformId)->delete();

                continue;
            }

            $account->socialLinks()->updateOrCreate(
                ['social_platform_id' => $platformId],
                ['url' => $url]
            );
        }
    }

    protected function saveLocations(Account $account): void
    {
        $submittedIds = [];
        foreach ($this->locations as $index => $loc) {
            if (empty($loc['country_id']) || empty($loc['address'])) {
                continue;
            }

            $attributes = [
                'account_id' => $account->id,
                'location_type' => $index === 0 ? 'primary' : 'branch',
                'country_id' => $loc['country_id'],
                'state_id' => $loc['state_id'] ?: null,
                'city_id' => $loc['city_id'] ?: null,
                'address_line_1' => $loc['address'],
                'is_primary' => $index === 0,
                'is_active' => true,
                'created_by_user_id' => Auth::id(),
            ];

            $existing = ! empty($loc['id'])
                ? AccountLocation::where('id', $loc['id'])->where('account_id', $account->id)->first()
                : null;

            if ($existing) {
                $existing->update($attributes);
                $location = $existing;
            } else {
                $location = AccountLocation::create($attributes);
            }

            $submittedIds[] = $location->id;
        }
        $account->locations()->whereNotIn('id', $submittedIds)->delete();
    }

    protected function saveGalleryAndVideos(Account $account): void
    {
        foreach ($this->gallery_files as $file) {
            $path = $file->store('suppliers/gallery', 'public');
            SupplierGallery::create([
                'supplier_account_id' => $account->id,
                'image_path' => $path,
                'created_by_user_id' => Auth::id(),
            ]);
        }
        $this->gallery_files = [];

        foreach ($this->video_urls as $url) {
            $url = trim($url);
            if (! $url) {
                continue;
            }
            $id = $this->extractYouTubeId($url);
            if ($id) {
                SupplierVideo::create([
                    'supplier_account_id' => $account->id,
                    'provider' => 'youtube',
                    'video_id' => $id,
                    'video_url' => $url,
                    'created_by_user_id' => Auth::id(),
                ]);
            }
        }
        $this->video_urls = [];
    }

    protected function extractYouTubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);

        return $matches[1] ?? null;
    }

    /* ─────────────────── Step 7: Plan Selection + Final Review ─────────────────── */

    /**
     * Marks a plan as chosen and reveals the review panel — this alone does
     * not submit anything. Nothing is written to the database until the
     * applicant explicitly clicks Final Submit (free plan) or Proceed to
     * Payment (paid plan, which itself only submits the capability once
     * Stripe confirms payment in CheckoutController::success()).
     */
    public function selectPlan(int $planId): void
    {
        $plan = SubscriptionPlan::find($planId);

        if (! $plan || ! $plan->is_active) {
            $this->addError('plan', 'This plan is not available.');
            return;
        }

        $this->resetErrorBag('plan');
        $this->selectedPlanId = $planId;
    }

    /**
     * The Final Submit action for a free plan — the only point in the whole
     * wizard where the application actually moves from draft to pending.
     */
    public function confirmFreePlanSubmission(SubscriptionSelectionService $selection, CapabilityApplicationService $capabilityService): void
    {
        if (! $this->selectedPlanId) {
            $this->addError('plan', 'Please select a plan before submitting.');
            return;
        }

        $account = $this->account();
        $plan = SubscriptionPlan::findOrFail($this->selectedPlanId);

        if (! $plan->isFree()) {
            $this->addError('plan', 'Please use the payment button for a paid plan.');
            return;
        }

        try {
            $selection->select($account, $plan, Auth::user());
            $capabilityService->submit($account, 'supplier', Auth::user());
        } catch (\Throwable $e) {
            $this->addError('plan', $e->getMessage());
            return;
        }

        // A dual buyer+supplier registration finishes Supplier onboarding
        // first — its shared fields were just copied into the still-draft
        // BuyerProfile (CapabilityApplicationService::submit()), so send
        // the user straight into the (now pre-filled) Buyer wizard instead
        // of the supplier "pending review" holding page.
        if ($account->buyerCapability?->status === 'draft') {
            session()->flash('success', "Supplier application submitted! Let's finish setting up your buyer account too.");
            $this->redirect(route('buyer.onboarding.profile'), navigate: false);
            return;
        }

        session()->flash('success', 'Application submitted! We\'ll review it and get back to you soon.');
        $this->redirect(route('supplier.pending'), navigate: false);
    }

    /* ─────────────────── Render ─────────────────── */

    public function render()
    {
        $documentTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'supplier')))
            ->with(['capabilityEnables' => fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'supplier'))])
            ->orderBy('sort_order')
            ->get()
            ->each(fn ($dt) => $dt->is_required = (bool) $dt->capabilityEnables->first()?->is_required);

        $account = $this->account();

        $currentDocs = SupplierDocument::with('documentType')
            ->where('supplier_account_id', $account->id)
            ->where('is_current', true)
            ->whereNotNull('document_type_id')
            ->get()
            ->keyBy('document_type_id');

        $customDocs = SupplierDocument::where('supplier_account_id', $account->id)
            ->whereNull('document_type_id')
            ->where('is_current', true)
            ->get();

        $existingGalleryImages = $account->galleryImages()->orderBy('id')->get();
        $existingVideos = $account->videos()->orderBy('id')->get();

        $allPlans = SubscriptionPlan::where('is_active', true)->get();
        $profile = $account->supplierProfile;

        return view('livewire.auth.supplier-application', [
            'socialPlatforms' => SocialPlatform::active()->get(),
            'supplierTypes' => SupplierType::where('is_active', true)->orderBy('sort_order')->get(),
            'exhibitions' => Exhibition::where('is_active', true)->orderBy('sort_order')->get(),
            'countries' => Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'flag']),
            'legalEntityTypes' => ['Sole Proprietorship', 'Partnership', 'Limited Company', 'Corporation', 'Government Entity'],
            'requiredDocumentTypes' => $documentTypes->where('is_required', true)->values(),
            'optionalDocumentTypes' => $documentTypes->where('is_required', false)->values(),
            'currentDocs' => $currentDocs,
            'customDocs' => $customDocs,
            'existingLogo' => $profile?->logo,
            'existingBanner' => $profile?->banner,
            'existingProfilePhoto' => $profile?->profile_photo,
            'existingGalleryImages' => $existingGalleryImages,
            'existingVideos' => $existingVideos,
            'reviewSelectedSupplierTypes' => SupplierType::whereIn('id', array_values(array_filter(array_map('intval', (array) $this->supplier_type_ids))))->pluck('name'),
            'reviewSelectedPlan' => $this->selectedPlanId ? SubscriptionPlan::find($this->selectedPlanId) : null,
            'monthlyPlans' => $allPlans->where('billing_type', 'monthly')->values(),
            'yearlyPlans' => $allPlans->where('billing_type', 'yearly')->values(),
            'freePlans' => $allPlans->where('billing_type', 'free')->values(),
        ])->layout('components.layouts.auth', [
            'title' => 'Supplier Application',
            'maxWidth' => 'max-w-3xl',
        ]);
    }
}
