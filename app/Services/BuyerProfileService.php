<?php

namespace App\Services;

use App\Models\AccountCapability;
use App\Models\BuyerProfile;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BuyerProfileService
{
    /**
     * Save partial data without marking the profile complete.
     */
    public function saveDraft(\App\Models\Account $account, array $data): BuyerProfile
    {
        /** @var BuyerProfile $profile */
        $profile = $account->buyerProfile ?? BuyerProfile::create(['account_id' => $account->id]);

        $logoPath = $this->handleLogoUpload($data, $profile);

        $buyerTypeIds = array_filter(array_map('intval', (array) ($data['buyer_type_ids'] ?? [])));
        if (! empty($buyerTypeIds)) {
            $pivotData = [];
            foreach ($buyerTypeIds as $index => $typeId) {
                $pivotData[$typeId] = ['is_primary' => ($index === 0)];
            }
            $account->buyerTypes()->sync($pivotData);
            $profile->buyer_type_id = $buyerTypeIds[0];
        }

        $profile->fill(array_filter([
            'buyer_type_id'     => $profile->buyer_type_id,
            'display_name'      => filled($data['display_name'] ?? null) ? trim($data['display_name']) : $profile->display_name,
            'organization_name' => filled($data['organization_name'] ?? null) ? trim($data['organization_name']) : $profile->organization_name,
            'contact_person'    => filled($data['contact_person'] ?? null) ? trim($data['contact_person']) : $profile->contact_person,
            'position'          => $data['position']         ?? $profile->position,
            'email'             => filled($data['email'] ?? null) ? strtolower(trim($data['email'])) : $profile->email,
            'phone'             => $data['phone']             ?? $profile->phone,
            'website'           => $data['website']           ?? $profile->website,
            'country_id'        => $data['country_id']        ?? $profile->country_id,
            'state_id'          => $data['state_id']          ?? $profile->state_id,
            'city_id'           => $data['city_id']           ?? $profile->city_id,
            'address'           => $data['address']           ?? $profile->address,
            'tax_id'            => $data['tax_id']            ?? $profile->tax_id,
            'bio'               => $data['bio']               ?? $profile->bio,
            'procurement_info'  => $data['procurement_info']  ?? $profile->procurement_info,
            'logo'              => $logoPath                  ?? $profile->logo,
        ], fn ($v) => $v !== null));

        // Do NOT set profile_completed_at on draft
        $profile->save();

        return $profile;
    }

    /**
     * Validate required fields and mark the profile as complete.
     */
    public function completeProfile(\App\Models\Account $account, array $data): BuyerProfile
    {
        $profile = $this->saveDraft($account, $data);

        // Required field validation
        $this->assertComplete($profile, $account, $data);

        $profile->update(['profile_completed_at' => now()]);

        return $profile;
    }

    /**
     * Throw a validation exception if required fields are missing.
     */
    private function assertComplete(BuyerProfile $profile, \App\Models\Account $account, array $data = []): void
    {
        $errors = [];

        $hasBuyerTypes = $account->buyerTypes()->exists() || ! empty($data['buyer_type_ids']);
        if (! $hasBuyerTypes && blank($profile->buyer_type_id)) {
            $errors['buyer_type_ids'] = 'Please select at least one buyer type.';
        }

        if (blank($profile->display_name))   $errors['display_name']   = 'Display name is required.';
        if (blank($profile->contact_person)) $errors['contact_person'] = 'Contact person is required.';
        if (blank($profile->email))          $errors['email']          = 'Contact email is required.';
        if (blank($profile->country_id))     $errors['country_id']     = 'Country is required.';
        if (blank($profile->address))        $errors['address']        = 'Address is required.';

        if ($account->isOrganization()) {
            if (blank($profile->organization_name)) $errors['organization_name'] = 'Organisation name is required.';
        }

        if (count($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * Handle logo upload via Livewire TemporaryUploadedFile or keep existing.
     */
    private function handleLogoUpload(array $data, BuyerProfile $profile): ?string
    {
        $file = $data['logo'] ?? null;

        if (! $file || ! is_object($file)) {
            return null; // keep existing
        }

        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $img     = $manager->read($file->getRealPath());
            $img->cover(512, 512);

            $ext  = strtolower($file->getClientOriginalExtension());
            $ext  = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';
            $path = 'buyer-logos/' . Str::random(40) . '.' . $ext;

            $encoded = match ($ext) {
                'png'  => $img->toPng(),
                'webp' => $img->toWebp(),
                default => $img->toJpeg(85),
            };

            Storage::disk('public')->put($path, $encoded->toString());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
