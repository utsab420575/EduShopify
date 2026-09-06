<?php

namespace App\Livewire\Buyer;

use App\Models\Account;
use App\Models\AccountLocation;
use App\Models\BuyerGallery;
use App\Models\BuyerType;
use App\Models\City;
use App\Models\Country;
use App\Models\SocialPlatform;
use App\Models\State;
use App\Services\BuyerProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class BuyerProfileManager extends Component
{
    use WithFileUploads;

    /* ── Sections open/close (multiple allowed) ── */
    public array $openSections = ['company'];

    /* ── Company Info ── */
    public array $buyer_type_ids = [];
    public string $display_name = '';
    public string $organization_name = '';
    public string $bio = '';
    public string $procurement_info = '';

    /* ── Contact ── */
    public string $contact_person = '';
    public string $position = '';
    public string $email = '';
    public string $phone = '';
    public string $website = '';
    public string $tax_id = '';

    /* ── Media ── */
    public $logo_upload;
    public $profile_photo_upload;
    public $new_gallery_files = [];
    public array $gallery_files = [];

    /* ── Social Links ── */
    public array $social_links = [];

    /* ── Locations ── */
    public array $locations = [];
    public array $new_location = [
        'location_type'  => 'branch',
        'label'          => '',
        'contact_name'   => '',
        'phone'          => '',
        'country_id'     => null,
        'state_id'       => null,
        'city_id'        => null,
        'address_line_1' => '',
        'address_line_2' => '',
        'postal_code'    => '',
        'is_primary'     => false,
        'states'         => [],
        'cities'         => [],
    ];

    protected const PREVIEWABLE = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    /* ─────────────── Lifecycle ─────────────── */

    public function mount(): void
    {
        $account = Auth::user()->account;
        if (! $account || ! $account->buyerCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }
        $account->load([
            'buyerProfile.country', 'buyerProfile.state', 'buyerProfile.city',
            'buyerTypes',
            'buyerGalleryImages',
            'socialLinks.platform',
            'locations.country', 'locations.state', 'locations.city',
        ]);

        $profile = $account->buyerProfile;

        $this->buyer_type_ids    = $account->buyerTypes->pluck('id')->toArray();
        $this->display_name      = $profile?->display_name ?? '';
        $this->organization_name = $profile?->organization_name ?? '';
        $this->bio               = $profile?->bio ?? '';
        $this->procurement_info  = $profile?->procurement_info ?? '';

        $this->contact_person = $profile?->contact_person ?? '';
        $this->position       = $profile?->position ?? '';
        $this->email          = $profile?->email ?? '';
        $this->phone          = $profile?->phone ?? '';
        $this->website        = $profile?->website ?? '';
        $this->tax_id         = $profile?->tax_id ?? '';

        $this->social_links = $account->socialLinks->map(fn ($l) => [
            'platform_id' => $l->social_platform_id,
            'url'         => $l->url ?? '',
            'label'       => $l->label ?? '',
        ])->toArray();

        $this->locations = $account->locations
            ->map(fn (AccountLocation $loc) => $this->hydrateLocation($loc))
            ->toArray();
    }

    public function render()
    {
        $account = Auth::user()->account;
        $account->load([
            'buyerProfile.country', 'buyerProfile.state', 'buyerProfile.city',
            'primaryOwner',
        ]);

        return view('livewire.buyer.buyer-profile-manager', [
            'account'         => $account,
            'profile'         => $account->buyerProfile,
            'countries'       => Country::active()->orderBy('name')->get(['id', 'name']),
            'buyerTypes'      => BuyerType::active()->orderBy('name')->get(['id', 'name']),
            'socialPlatforms' => SocialPlatform::active()->get(),
            'existingGallery' => $account->buyerGalleryImages()->active()->get(),
        ]);
    }

    /* ─────────────── Section toggle ─────────────── */

    public function toggleSection(string $section): void
    {
        if (in_array($section, $this->openSections)) {
            $this->openSections = array_values(array_filter($this->openSections, fn ($s) => $s !== $section));
        } else {
            $this->openSections[] = $section;
        }
    }

    /* ─────────────── Cascading dropdowns ─────────────── */

    public function updated($name, $value): void
    {
        if ($name === 'new_location.country_id') {
            $this->new_location['states']   = $value ? State::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->new_location['cities']   = [];
            $this->new_location['state_id'] = null;
            $this->new_location['city_id']  = null;
        }
        if ($name === 'new_location.state_id') {
            $this->new_location['cities']  = $value ? City::where('state_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->new_location['city_id'] = null;
        }
        if (Str::is('locations.*.country_id', $name)) {
            $i = (int) explode('.', $name)[1];
            $this->locations[$i]['states']   = $value ? State::where('country_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->locations[$i]['cities']   = [];
            $this->locations[$i]['state_id'] = null;
            $this->locations[$i]['city_id']  = null;
        }
        if (Str::is('locations.*.state_id', $name)) {
            $i = (int) explode('.', $name)[1];
            $this->locations[$i]['cities']  = $value ? City::where('state_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
            $this->locations[$i]['city_id'] = null;
        }
    }

    /* ─────────────── Save: Company Info ─────────────── */

    public function saveCompany(BuyerProfileService $svc): void
    {
        $this->validate(
            [
                'display_name'      => 'required|string|max:200',
                'organization_name' => 'nullable|string|max:200',
                'buyer_type_ids'    => 'required|array|min:1',
                'buyer_type_ids.*'  => 'exists:buyer_types,id',
                'bio'               => 'nullable|string|max:2000',
                'procurement_info'  => 'nullable|string|max:2000',
            ],
            [
                'display_name.required'   => 'Please enter a display name.',
                'buyer_type_ids.required' => 'Please select at least one buyer type.',
                'buyer_type_ids.min'      => 'Please select at least one buyer type.',
            ]
        );

        $svc->saveDraft($this->account(), [
            'display_name'      => $this->display_name,
            'organization_name' => $this->organization_name,
            'buyer_type_ids'    => $this->buyer_type_ids,
            'bio'               => $this->bio,
            'procurement_info'  => $this->procurement_info,
        ]);

        session()->flash('success', 'Company information saved.');
    }

    /* ─────────────── Save: Contact ─────────────── */

    public function saveContact(BuyerProfileService $svc): void
    {
        $this->validate(
            [
                'contact_person' => 'required|string|max:150',
                'email'          => 'required|email|max:150',
                'position'       => 'nullable|string|max:100',
                'phone'          => 'nullable|string|max:30',
                'website'        => 'nullable|url|max:255',
                'tax_id'         => 'nullable|string|max:100',
            ],
            [
                'contact_person.required' => 'Please enter a contact person.',
                'email.required'          => 'Please enter a contact email.',
            ]
        );

        $svc->saveDraft($this->account(), [
            'contact_person' => $this->contact_person,
            'position'       => $this->position,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'website'        => $this->website,
            'tax_id'         => $this->tax_id,
        ]);

        session()->flash('success', 'Contact information saved.');
    }

    /* ─────────────── Upload Hooks ─────────────── */

    public function updatedLogoUpload(): void
    {
        if ($this->logo_upload && ! in_array(strtolower($this->logo_upload->getClientOriginalExtension()), self::PREVIEWABLE, true)) {
            $this->logo_upload = null;
            $this->addError('logo_upload', 'Unsupported format. Use JPG, PNG, WEBP, or GIF.');
        }
    }

    public function updatedProfilePhotoUpload(): void
    {
        if ($this->profile_photo_upload && ! in_array(strtolower($this->profile_photo_upload->getClientOriginalExtension()), self::PREVIEWABLE, true)) {
            $this->profile_photo_upload = null;
            $this->addError('profile_photo_upload', 'Unsupported format. Use JPG, PNG, WEBP, or GIF.');
        }
    }

    public function updatedNewGalleryFiles(): void
    {
        foreach ((array) $this->new_gallery_files as $file) {
            if ($file && in_array(strtolower($file->getClientOriginalExtension()), self::PREVIEWABLE, true) && count($this->gallery_files) < 20) {
                $this->gallery_files[] = $file;
            }
        }
        $this->new_gallery_files = [];
    }

    public function removeGalleryFile(int $index): void
    {
        array_splice($this->gallery_files, $index, 1);
        $this->gallery_files = array_values($this->gallery_files);
    }

    public function removeExistingGalleryImage(int $id): void
    {
        $image = BuyerGallery::where('buyer_account_id', $this->account()->id)->find($id);
        if ($image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    /* ─────────────── Save: Media ─────────────── */

    public function saveMedia(): void
    {
        $this->validate([
            'logo_upload'          => 'nullable|image|max:5120',
            'profile_photo_upload' => 'nullable|image|max:5120',
            'gallery_files.*'      => 'image|max:5120',
        ]);

        $account = $this->account();
        $profile = $account->buyerProfile;

        if (! $profile) {
            $profile = new \App\Models\BuyerProfile(['account_id' => $account->id]);
        }

        if ($this->logo_upload) {
            if ($profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }
            $profile->logo = $this->logo_upload->store('buyers/logos', 'public');
            $this->logo_upload = null;
        }

        if ($this->profile_photo_upload) {
            if ($profile->profile_photo) {
                Storage::disk('public')->delete($profile->profile_photo);
            }
            $profile->profile_photo = $this->profile_photo_upload->store('buyers/photos', 'public');
            $this->profile_photo_upload = null;
        }

        $profile->save();

        foreach ($this->gallery_files as $file) {
            $path = $file->store('buyers/gallery', 'public');
            BuyerGallery::create([
                'buyer_account_id'   => $account->id,
                'image_path'         => $path,
                'is_active'          => true,
                'created_by_user_id' => Auth::id(),
            ]);
        }
        $this->gallery_files = [];

        session()->flash('success', 'Media saved.');
    }

    /* ─────────────── Social Links ─────────────── */

    public function addSocialLink(): void
    {
        $this->social_links[] = ['platform_id' => null, 'url' => '', 'label' => ''];
    }

    public function removeSocialLink(int $index): void
    {
        array_splice($this->social_links, $index, 1);
        $this->social_links = array_values($this->social_links);
    }

    public function saveSocialLinks(): void
    {
        $this->validate([
            'social_links.*.url'   => 'nullable|url|max:500',
            'social_links.*.label' => 'nullable|string|max:150',
        ]);

        $account   = $this->account();
        $submitted = [];

        foreach ($this->social_links as $row) {
            $pid = (int) ($row['platform_id'] ?? 0);
            $url = trim($row['url'] ?? '');
            if (! $pid || ! $url) {
                continue;
            }
            $account->socialLinks()->updateOrCreate(
                ['social_platform_id' => $pid],
                ['url' => $url, 'label' => $row['label'] ?? null, 'is_active' => true]
            );
            $submitted[] = $pid;
        }

        $account->socialLinks()->whereNotIn('social_platform_id', $submitted)->delete();

        session()->flash('success', 'Social links saved.');
    }

    /* ─────────────── Locations ─────────────── */

    public function addLocation(): void
    {
        $this->validate(
            [
                'new_location.country_id'     => 'required|exists:countries,id',
                'new_location.address_line_1' => 'required|string|max:255',
                'new_location.location_type'  => 'required|in:primary,registered_office,branch,warehouse,showroom,billing,delivery',
            ],
            [
                'new_location.country_id.required'     => 'Please select a country.',
                'new_location.address_line_1.required' => 'Please enter an address.',
            ]
        );

        $account = $this->account();

        DB::transaction(function () use ($account) {
            if ($this->new_location['is_primary']) {
                $account->locations()->update(['is_primary' => false]);
            }

            $location = $account->locations()->create([
                'location_type'      => $this->new_location['location_type'],
                'label'              => $this->new_location['label'] ?: null,
                'contact_name'       => $this->new_location['contact_name'] ?: null,
                'phone'              => $this->new_location['phone'] ?: null,
                'country_id'         => $this->new_location['country_id'],
                'state_id'           => $this->new_location['state_id'] ?: null,
                'city_id'            => $this->new_location['city_id'] ?: null,
                'address_line_1'     => $this->new_location['address_line_1'],
                'address_line_2'     => $this->new_location['address_line_2'] ?: null,
                'postal_code'        => $this->new_location['postal_code'] ?: null,
                'is_primary'         => $this->new_location['is_primary'] || ($account->locations()->count() === 1),
                'is_active'          => true,
                'created_by_user_id' => Auth::id(),
            ]);

            $this->locations[] = $this->hydrateLocation($location->load(['country', 'state', 'city']));
        });

        $this->resetNewLocationForm();
        session()->flash('success', 'Location added.');
    }

    public function updateLocation(int $id): void
    {
        $i = collect($this->locations)->search(fn ($l) => $l['id'] === $id);
        if ($i === false) {
            return;
        }

        $this->validate([
            "locations.{$i}.country_id"     => 'required|exists:countries,id',
            "locations.{$i}.address_line_1" => 'required|string|max:255',
        ]);

        $loc      = $this->locations[$i];
        $location = AccountLocation::where('account_id', $this->account()->id)->findOrFail($id);

        $location->update([
            'location_type'  => $loc['location_type'],
            'label'          => $loc['label'] ?: null,
            'contact_name'   => $loc['contact_name'] ?: null,
            'phone'          => $loc['phone'] ?: null,
            'country_id'     => $loc['country_id'],
            'state_id'       => $loc['state_id'] ?: null,
            'city_id'        => $loc['city_id'] ?: null,
            'address_line_1' => $loc['address_line_1'],
            'address_line_2' => $loc['address_line_2'] ?: null,
            'postal_code'    => $loc['postal_code'] ?: null,
        ]);

        $this->locations[$i] = $this->hydrateLocation($location->load(['country', 'state', 'city']));
        session()->flash('success', 'Location updated.');
    }

    public function removeLocation(int $id): void
    {
        AccountLocation::where('account_id', $this->account()->id)->findOrFail($id)->delete();
        $this->locations = array_values(array_filter($this->locations, fn ($l) => $l['id'] !== $id));
        session()->flash('success', 'Location removed.');
    }

    public function makePrimaryLocation(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->account()->locations()->update(['is_primary' => false]);
            AccountLocation::where('account_id', $this->account()->id)->findOrFail($id)->update(['is_primary' => true]);
        });

        foreach ($this->locations as &$loc) {
            $loc['is_primary'] = ($loc['id'] === $id);
        }

        session()->flash('success', 'Primary location updated.');
    }

    /* ─────────────── Helpers ─────────────── */

    protected function account(): Account
    {
        return Auth::user()->account;
    }

    protected function hydrateLocation(AccountLocation $loc): array
    {
        return [
            'id'             => $loc->id,
            'location_type'  => $loc->location_type,
            'label'          => $loc->label ?? '',
            'contact_name'   => $loc->contact_name ?? '',
            'phone'          => $loc->phone ?? '',
            'is_primary'     => $loc->is_primary,
            'country_id'     => $loc->country_id,
            'state_id'       => $loc->state_id,
            'city_id'        => $loc->city_id,
            'address_line_1' => $loc->address_line_1 ?? '',
            'address_line_2' => $loc->address_line_2 ?? '',
            'postal_code'    => $loc->postal_code ?? '',
            'country_name'   => $loc->country?->name ?? '',
            'state_name'     => $loc->state?->name ?? '',
            'city_name'      => $loc->city?->name ?? '',
            'states'         => $loc->country_id
                ? State::where('country_id', $loc->country_id)->orderBy('name')->get(['id', 'name'])->toArray()
                : [],
            'cities'         => $loc->state_id
                ? City::where('state_id', $loc->state_id)->orderBy('name')->get(['id', 'name'])->toArray()
                : [],
        ];
    }

    protected function resetNewLocationForm(): void
    {
        $this->new_location = [
            'location_type'  => 'branch',
            'label'          => '',
            'contact_name'   => '',
            'phone'          => '',
            'country_id'     => null,
            'state_id'       => null,
            'city_id'        => null,
            'address_line_1' => '',
            'address_line_2' => '',
            'postal_code'    => '',
            'is_primary'     => false,
            'states'         => [],
            'cities'         => [],
        ];
    }
}
