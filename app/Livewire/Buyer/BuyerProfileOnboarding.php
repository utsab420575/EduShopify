<?php

namespace App\Livewire\Buyer;

use App\Models\Account;
use App\Models\AccountLocation;
use App\Models\BuyerDocument;
use App\Models\BuyerGallery;
use App\Models\BuyerType;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\SocialPlatform;
use App\Models\State;
use App\Services\BuyerDocumentService;
use App\Services\BuyerProfileService;
use App\Services\CapabilityApplicationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * 3-step Buyer onboarding wizard (2 steps if no buyer-scoped document types
 * are configured — see totalSteps in mount()): Company Info → Branding &
 * Media → Documents (conditional). Mirrors the structure and step-tracking
 * mechanism already built and tested for App\Livewire\Auth\SupplierApplication.
 */
class BuyerProfileOnboarding extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 3;
    public int $maxStepReached = 1;

    /* ── Step 1: Company Information ── */
    public array $buyer_type_ids = [];
    public string $display_name = '';
    public string $organization_name = '';
    public string $contact_person = '';
    public string $position = '';
    public string $email = '';
    public string $phone = '';
    public string $website = '';
    public array $locations = [];
    public string $tax_id = '';
    public string $bio = '';
    public string $procurement_info = '';

    /* ── Step 2: Branding & Media ── */
    public $logo;
    public $profile_photo;
    public array $gallery_files = [];
    /** Repeatable rows: ['platform_id' => int|null, 'url' => string]. */
    public array $social_links = [];

    /* ── Step 3: Verification Documents (conditional) ── */
    public string $new_document_type_id = '';
    public $new_file;
    public string $new_expiry = '';
    public string $new_custom_name = '';

    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->buyerCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        $capabilityStatus = $account->capabilityStatus('buyer');

        if (! in_array($capabilityStatus, ['draft', 'revision_required'], true)) {
            $this->redirect(route('buyer.dashboard'), navigate: false);
            return;
        }

        // Documents step only exists if an admin has actually configured at
        // least one buyer-scoped document type — otherwise this is a plain
        // 2-step wizard.
        $this->totalSteps = $this->buyerDocumentTypesConfigured() ? 3 : 2;

        $this->buyer_type_ids = $account->buyerTypes()->pluck('buyer_types.id')->toArray();

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

        $profile = $account->buyerProfile;
        if ($profile) {
            if (empty($this->buyer_type_ids) && $profile->buyer_type_id) {
                $this->buyer_type_ids = [(int) $profile->buyer_type_id];
            }
            $this->display_name = $profile->display_name ?? '';
            $this->organization_name = $profile->organization_name ?? '';
            $this->contact_person = $profile->contact_person ?? '';
            $this->position = $profile->position ?? '';
            $this->email = $profile->email ?? '';
            $this->phone = $profile->phone ?? '';
            $this->website = $profile->website ?? '';
            $this->tax_id = $profile->tax_id ?? '';
            $this->bio = $profile->bio ?? '';
            $this->procurement_info = $profile->procurement_info ?? '';

            // No AccountLocation rows yet (profile predates the multi-location
            // feature, or was never saved) but the flat legacy columns have
            // data — seed the single primary row from them instead of
            // showing an empty form.
            if (empty($account->locations->count()) && $profile->country_id) {
                $this->locations = [[
                    'id' => null,
                    'country_id' => $profile->country_id,
                    'state_id' => $profile->state_id,
                    'city_id' => $profile->city_id,
                    'address' => $profile->address ?? '',
                    'states' => State::where('country_id', $profile->country_id)->orderBy('name')->get(['id', 'name'])->toArray(),
                    'cities' => $profile->state_id
                        ? City::where('state_id', $profile->state_id)->orderBy('name')->get(['id', 'name'])->toArray()
                        : City::where('country_id', $profile->country_id)->orderBy('name')->get(['id', 'name'])->toArray(),
                ]];
            }
        }

        if ($this->website && ! str_starts_with($this->website, 'http')) {
            $this->website = 'https://'.$this->website;
        }

        $this->social_links = $account->socialLinks->map(fn ($link) => [
            'platform_id' => $link->social_platform_id,
            'url' => $link->url,
            'label' => $link->label ?? '',
        ])->values()->toArray();

        // Resume exactly where they left off — same mechanism as the
        // Supplier wizard (see SupplierApplication::mount()).
        if ($profile && $profile->max_step_reached !== null) {
            $this->maxStepReached = min($profile->max_step_reached, $this->totalSteps);
            $this->step = max(1, min($profile->current_step ?? $this->maxStepReached, $this->totalSteps));
        } elseif ($profile && $profile->isComplete()) {
            $this->maxStepReached = $this->totalSteps;
            $this->step = $this->totalSteps;
        }

        if ($capabilityStatus === 'revision_required') {
            $this->maxStepReached = $this->totalSteps;
        }
    }

    protected function buyerDocumentTypesConfigured(): bool
    {
        return DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'buyer')))
            ->exists();
    }

    protected function account(): Account
    {
        return Auth::user()->account;
    }

    protected function emptyLocationRow(): array
    {
        return ['id' => null, 'country_id' => null, 'state_id' => null, 'city_id' => null, 'address' => '', 'states' => [], 'cities' => []];
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

    public function updatedWebsite($value): void
    {
        if ($value && ! str_starts_with($value, 'http')) {
            $this->website = 'https://'.$value;
        }
    }

    /* ─────────────────── Image upload guards ─────────────────── */

    /**
     * Formats Livewire can actually generate a temporaryUrl() preview for
     * (see config('livewire.temporary_file_upload.preview_mimes')). A format
     * outside this list — AVIF, HEIC, etc. — doesn't fail Livewire's own
     * upload validation, it uploads fine and only blows up later with a raw
     * FileNotPreviewableException the moment the Blade view tries to render
     * a thumbnail. Rejecting it immediately here, before that render ever
     * happens, turns that crash into a normal, friendly validation error.
     */
    protected const PREVIEWABLE_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    protected function guardPreviewableImage(string $property): void
    {
        $file = $this->{$property};

        if (! $file) {
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        $this->resetErrorBag($property);

        if (! in_array($extension, self::PREVIEWABLE_IMAGE_EXTENSIONS, true)) {
            $this->{$property} = null;
            $this->addError($property, 'This image format ("'.strtoupper($extension).'") isn\'t supported. Please upload a JPG, PNG, GIF, WEBP, or BMP file.');
        }
    }

    public function updatedProfilePhoto(): void
    {
        $this->guardPreviewableImage('profile_photo');
    }

    public function updatedLogo(): void
    {
        $this->guardPreviewableImage('logo');
    }

    public function updatedGalleryFiles(): void
    {
        $valid = [];
        $rejected = false;

        foreach ($this->gallery_files as $file) {
            if ($file && in_array(strtolower($file->getClientOriginalExtension()), self::PREVIEWABLE_IMAGE_EXTENSIONS, true)) {
                $valid[] = $file;
            } else {
                $rejected = true;
            }
        }

        $this->gallery_files = $valid;

        $this->resetErrorBag('gallery_files');

        if ($rejected) {
            $this->addError('gallery_files', 'One or more images use an unsupported format. Please upload JPG, PNG, GIF, WEBP, or BMP files.');
        }
    }

    /* ─────────────────── Buyer Types ─────────────────── */

    public function toggleBuyerType(int $id): void
    {
        $current = array_values(array_filter(array_map('intval', (array) $this->buyer_type_ids)));
        if (in_array($id, $current, true)) {
            $this->buyer_type_ids = array_values(array_diff($current, [$id]));
        } else {
            $this->buyer_type_ids = array_values(array_unique(array_merge($current, [$id])));
        }
    }

    public function toggleAllBuyerTypes(): void
    {
        $allIds = BuyerType::where('is_active', true)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $current = array_values(array_filter(array_map('intval', (array) $this->buyer_type_ids)));

        if (count($current) >= count($allIds) && count($allIds) > 0) {
            $this->buyer_type_ids = [];
        } else {
            $this->buyer_type_ids = $allIds;
        }
    }

    /* ─────────────────── Gallery ─────────────────── */

    public function removeGalleryFile(int $index): void
    {
        array_splice($this->gallery_files, $index, 1);
        $this->gallery_files = array_values($this->gallery_files);
    }

    public function removeExistingGalleryImage(int $id): void
    {
        $image = BuyerGallery::where('buyer_account_id', $this->account()->id)->find($id);

        if (! $image) {
            return;
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }

    /* ─────────────────── Social Media (repeatable, optional) ─────────────────── */

    public function addSocialLink(): void
    {
        $this->social_links[] = ['platform_id' => null, 'url' => '', 'label' => ''];
    }

    public function removeSocialLink(int $index): void
    {
        array_splice($this->social_links, $index, 1);
        $this->social_links = array_values($this->social_links);
    }

    /**
     * Saves each row with both a platform and a URL as its own social_links
     * row (one per platform per account, enforced by a DB unique index).
     * Rows removed since the last save are deleted rather than left stale.
     */
    protected function saveSocialLinks(Account $account): void
    {
        $submittedPlatformIds = [];

        foreach ($this->social_links as $row) {
            $platformId = $row['platform_id'] ?? null;
            $url = trim($row['url'] ?? '');

            if (! $platformId || $url === '') {
                continue;
            }

            $account->socialLinks()->updateOrCreate(
                ['social_platform_id' => $platformId],
                ['url' => $url, 'label' => trim($row['label'] ?? '') ?: null]
            );

            $submittedPlatformIds[] = $platformId;
        }

        $account->socialLinks()->whereNotIn('social_platform_id', $submittedPlatformIds)->delete();
    }

    protected function saveGallery(Account $account): void
    {
        foreach ($this->gallery_files as $file) {
            $path = $file->store('buyers/gallery', 'public');
            BuyerGallery::create([
                'buyer_account_id' => $account->id,
                'image_path' => $path,
                'created_by_user_id' => Auth::id(),
            ]);
        }
        $this->gallery_files = [];
    }

    /**
     * Once profile_photo/logo have been moved from Livewire's temporary
     * upload storage into permanent storage (via saveDraft() above), the
     * component must stop referencing the temporary upload objects — their
     * underlying temp files get cleaned up after a few minutes
     * (livewire.temporary_file_upload.max_upload_time), and calling
     * ->temporaryUrl() on one after that silently renders a broken image
     * instead of the now-permanently-saved photo. Clearing them here makes
     * every subsequent render fall back to $existingProfilePhoto/
     * $existingLogo, which always points at the current saved file.
     */
    protected function resetStep2Uploads(): void
    {
        $this->profile_photo = null;
        $this->logo = null;
    }

    /* ─────────────────── Documents (step 3, when it exists) ─────────────────── */

    public function openAddDocument(?int $documentTypeId = null): void
    {
        $this->resetValidation();
        $this->reset(['new_document_type_id', 'new_file', 'new_expiry', 'new_custom_name']);
        $this->new_document_type_id = $documentTypeId ? (string) $documentTypeId : '';
        $this->dispatch('open-add-document');
    }

    public function addDocument(BuyerDocumentService $service): void
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

    protected function requiredDocumentTypeIds()
    {
        return DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'buyer'))->where('is_required', true))
            ->pluck('id');
    }

    /** Non-throwing check used to decide whether to show the review/submit UI. */
    protected function requiredDocumentsSatisfied(): bool
    {
        if ($this->totalSteps < 3) {
            return true;
        }

        $account = $this->account();

        foreach ($this->requiredDocumentTypeIds() as $typeId) {
            $hasCurrent = BuyerDocument::where('documentable_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrent) {
                return false;
            }
        }

        return true;
    }

    /* ─────────────────── Step Navigation ─────────────────── */

    public function goToStep(int $target): void
    {
        if ($target >= 1 && $target <= $this->maxStepReached) {
            $this->step = $target;
            $this->persistStepProgress();
        }
    }

    public function nextStep(BuyerProfileService $profileService): void
    {
        $rules = $this->rulesForStep($this->step);
        if (! empty($rules)) {
            $this->validate($rules, $this->messagesForStep($this->step));
        }

        if ($this->step === 1) {
            // completeProfile() validates all required fields and marks
            // profile_completed_at — step 1 already collects everything it needs.
            $profileService->completeProfile($this->account(), $this->profileData());
        }

        if ($this->step === 2) {
            $profileService->saveDraft($this->account(), $this->profileData());
            $this->saveGallery($this->account());
            $this->saveSocialLinks($this->account());
            $this->resetStep2Uploads();
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

    public function saveDraft(BuyerProfileService $profileService): void
    {
        $profileService->saveDraft($this->account(), $this->profileData());
        session()->flash('success', 'Draft saved.');
    }

    protected function persistStepProgress(): void
    {
        $profile = $this->account()->buyerProfile;

        if (! $profile) {
            return;
        }

        $profile->update([
            'current_step' => $this->step,
            'max_step_reached' => max($this->maxStepReached, $profile->max_step_reached ?? 1),
        ]);
    }

    /* ─────────────────── Final Submit ─────────────────── */

    public function confirmFinalSubmit(BuyerProfileService $profileService, CapabilityApplicationService $capabilityService): void
    {
        // The final step never goes through nextStep() (there's no Continue
        // button once step === totalSteps), so its own field rules and save
        // haven't run yet — do that here first. For the 2-step case (no
        // documents step) this is step 2's profile_photo/logo/gallery; for
        // the 3-step case step 2 already saved via nextStep(), and step 3
        // has no field rules of its own (documents are saved individually
        // by addDocument() as they're uploaded).
        $rules = $this->rulesForStep($this->step);
        if (! empty($rules)) {
            $this->validate($rules, $this->messagesForStep($this->step));
        }

        if ($this->step === 2) {
            $profileService->saveDraft($this->account(), $this->profileData());
            $this->saveGallery($this->account());
            $this->saveSocialLinks($this->account());
            $this->resetStep2Uploads();
        }

        if (! $this->requiredDocumentsSatisfied()) {
            $this->addError('documents', 'Please upload all required documents before submitting.');
            return;
        }

        try {
            $capabilityService->submit($this->account(), 'buyer', Auth::user());
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('success', 'Welcome aboard! Your buyer account is ready to go.');
        $this->redirect(route('buyer.dashboard'), navigate: false);
    }

    /* ─────────────────── Validation ─────────────────── */

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => array_filter([
                'display_name' => 'required|string|max:200',
                'organization_name' => $this->account()->isOrganization() ? 'required|string|max:200' : 'nullable|string|max:200',
                'buyer_type_ids' => 'required|array|min:1',
                'buyer_type_ids.*' => 'exists:buyer_types,id',
                'contact_person' => 'required|string|max:150',
                'email' => 'required|email|max:150',
                'phone' => 'nullable|string|max:30',
                'website' => 'nullable|url|max:255',
                'locations' => 'required|array|min:1',
                'locations.*.country_id' => 'required|exists:countries,id',
                'locations.*.state_id' => 'nullable|exists:states,id',
                'locations.*.city_id' => 'nullable|exists:cities,id',
                'locations.*.address' => 'required|string|max:500',
                'tax_id' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:2000',
                'procurement_info' => 'nullable|string|max:2000',
            ]),
            2 => [
                'profile_photo' => ($this->account()->buyerProfile?->profile_photo ? 'nullable' : 'required').'|image|mimes:'.implode(',', self::PREVIEWABLE_IMAGE_EXTENSIONS).'|max:5120',
                'logo' => 'nullable|image|mimes:'.implode(',', self::PREVIEWABLE_IMAGE_EXTENSIONS).'|max:5120',
                'gallery_files' => 'nullable|array|max:10',
                'gallery_files.*' => 'image|mimes:'.implode(',', self::PREVIEWABLE_IMAGE_EXTENSIONS).'|max:5120',
                'social_links.*.platform_id' => 'nullable|exists:social_platforms,id',
                'social_links.*.url' => 'nullable|url|max:500',
                'social_links.*.label' => 'nullable|string|max:150',
            ],
            default => [],
        };
    }

    protected function messagesForStep(int $step): array
    {
        if ($step === 2) {
            $unsupportedFormatMessage = 'This image format isn\'t supported. Please upload a JPG, PNG, GIF, WEBP, or BMP file.';

            $messages = [
                'profile_photo.required' => 'Please upload a profile photo before continuing.',
                'profile_photo.image' => $unsupportedFormatMessage,
                'profile_photo.mimes' => $unsupportedFormatMessage,
                'logo.image' => $unsupportedFormatMessage,
                'logo.mimes' => $unsupportedFormatMessage,
                'gallery_files.*.image' => $unsupportedFormatMessage,
                'gallery_files.*.mimes' => $unsupportedFormatMessage,
            ];

            foreach ($this->social_links as $i => $row) {
                $messages["social_links.$i.url.url"] = 'Please enter a valid URL, e.g. https://example.com/yourpage.';
                $messages["social_links.$i.url.max"] = 'That URL is too long.';
                $messages["social_links.$i.label.max"] = 'That name is too long.';
            }

            return $messages;
        }

        if ($step !== 1) {
            return [];
        }

        $messages = [
            'display_name.required' => 'Please enter your company display name.',
            'organization_name.required' => 'Please enter your organization name.',
            'buyer_type_ids.required' => 'Please select at least one buyer type.',
            'contact_person.required' => 'Please enter a contact person.',
            'email.required' => 'Please enter a contact email.',
        ];

        foreach ($this->locations as $i => $loc) {
            $label = $i === 0 ? 'your primary location' : 'Location '.($i + 1);
            $messages["locations.$i.country_id.required"] = "Please select a country for $label.";
            $messages["locations.$i.address.required"] = "Please enter an address for $label.";
        }

        return $messages;
    }

    protected function profileData(): array
    {
        $primaryLocation = $this->locations[0] ?? null;

        $data = [
            'logo' => $this->logo,
            'profile_photo' => $this->profile_photo,
            'display_name' => $this->display_name,
            'organization_name' => $this->organization_name,
            'buyer_type_ids' => $this->buyer_type_ids,
            'buyer_type_id' => $this->buyer_type_ids[0] ?? null,
            'contact_person' => $this->contact_person,
            'position' => $this->position,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'country_id' => $primaryLocation['country_id'] ?? null,
            'state_id' => $primaryLocation['state_id'] ?? null,
            'city_id' => $primaryLocation['city_id'] ?? null,
            'address' => $primaryLocation['address'] ?? null,
            'tax_id' => $this->tax_id,
            'bio' => $this->bio,
            'procurement_info' => $this->procurement_info,
        ];

        $this->saveLocations($this->account());

        return $data;
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

    /* ─────────────────── Render ─────────────────── */

    public function render()
    {
        $account = $this->account();
        $profile = $account->buyerProfile;

        $documentTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'buyer')))
            ->with(['capabilityEnables' => fn ($q) => $q->whereHas('capabilityType', fn ($c) => $c->where('code', 'buyer'))])
            ->orderBy('sort_order')
            ->get()
            ->each(fn ($dt) => $dt->is_required = (bool) $dt->capabilityEnables->first()?->is_required);

        $currentDocs = BuyerDocument::with('documentType')
            ->where('documentable_id', $account->id)
            ->where('is_current', true)
            ->whereNotNull('document_type_id')
            ->get()
            ->keyBy('document_type_id');

        $customDocs = BuyerDocument::where('documentable_id', $account->id)
            ->whereNull('document_type_id')
            ->where('is_current', true)
            ->get();

        $requiredDocumentTypes = $documentTypes->where('is_required', true)->values();
        $optionalDocumentTypes = $documentTypes->where('is_required', false)->values();
        $allDocTypesForModal = $requiredDocumentTypes->concat($optionalDocumentTypes);
        $selectedType = ($this->new_document_type_id !== '' && $this->new_document_type_id !== 'other')
            ? $allDocTypesForModal->firstWhere('id', (int) $this->new_document_type_id)
            : null;

        return view('livewire.buyer.buyer-profile-onboarding', [
            'buyerTypes' => BuyerType::where('is_active', true)->orderBy('sort_order')->get(),
            'countries' => Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'flag']),
            'socialPlatforms' => SocialPlatform::active()->get(),
            'otherSocialPlatformId' => SocialPlatform::where('slug', 'other')->value('id'),
            'account' => $account,
            'existingLogo' => $profile?->logo,
            'existingProfilePhoto' => $profile?->profile_photo,
            'existingGalleryImages' => $account->buyerGalleryImages()->orderBy('id')->get(),
            'requiredDocumentTypes' => $requiredDocumentTypes,
            'optionalDocumentTypes' => $optionalDocumentTypes,
            'allDocTypesForModal' => $allDocTypesForModal,
            'selectedType' => $selectedType,
            'currentDocs' => $currentDocs,
            'customDocs' => $customDocs,
            'requiredDocsSatisfied' => $this->requiredDocumentsSatisfied(),
        ])->layout('components.layouts.auth', [
            'title' => 'Complete Your Profile',
            'maxWidth' => 'max-w-3xl',
        ]);
    }
}
