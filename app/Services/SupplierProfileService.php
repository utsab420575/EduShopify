<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SupplierProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierProfileService
{
    /**
     * Save partial profile details without completing onboarding.
     */
    public function saveDraft(Account $account, array $data): SupplierProfile
    {
        /** @var SupplierProfile $profile */
        $profile = $account->supplierProfile ?? SupplierProfile::create(['account_id' => $account->id]);

        $logoPath    = $this->handleFileUpload($data, 'logo', 'supplier-logos', $profile->logo);
        $bannerPath  = $this->handleFileUpload($data, 'banner', 'supplier-banners', $profile->banner);
        $photoPath   = $this->handleFileUpload($data, 'profile_photo', 'supplier-photos', $profile->profile_photo);

        // Sync supplier_supplier_type pivot
        $supplierTypeIds = array_filter(array_map('intval', (array) ($data['supplier_type_ids'] ?? [])));
        if (! empty($supplierTypeIds)) {
            $pivotData = [];
            foreach ($supplierTypeIds as $index => $typeId) {
                $pivotData[$typeId] = ['is_primary' => ($index === 0)];
            }
            $account->supplierTypes()->sync($pivotData);
        }

        $profile->fill(array_filter([
            'display_name'      => filled($data['display_name'] ?? null) ? trim($data['display_name']) : $profile->display_name,
            'legal_name'        => filled($data['legal_name'] ?? null) ? trim($data['legal_name']) : $profile->legal_name,
            'legal_entity_type' => $data['legal_entity_type'] ?? $profile->legal_entity_type,
            'company_type'      => $data['legal_entity_type'] ?? $profile->company_type, // legacy mirror
            'contact_person'    => filled($data['contact_person'] ?? null) ? trim($data['contact_person']) : $profile->contact_person,
            'contact_email'     => filled($data['contact_email'] ?? null) ? strtolower(trim($data['contact_email'])) : $profile->contact_email,
            'contact_phone'     => $data['contact_phone']     ?? $profile->contact_phone,
            'whatsapp'          => $data['whatsapp']          ?? $profile->whatsapp,
            'support_email'     => $data['support_email']     ?? $profile->support_email,
            'website'           => $data['website']           ?? $profile->website,
            'founded_year'      => $data['founded_year']      ?? $profile->founded_year,
            'employees'         => $data['employees']         ?? $profile->employees,
            'country_id'        => $data['country_id']        ?? $profile->country_id,
            'state_id'          => $data['state_id']          ?? $profile->state_id,
            'city_id'           => $data['city_id']           ?? $profile->city_id,
            'address'           => $data['address']           ?? $profile->address,
            'description'       => $data['description']       ?? $profile->description,
            'logo'              => $logoPath                  ?? $profile->logo,
            'banner'            => $bannerPath                ?? $profile->banner,
            'profile_photo'     => $photoPath                 ?? $profile->profile_photo,
        ], fn ($v) => $v !== null));

        $profile->save();

        return $profile;
    }

    /**
     * Validate required fields and mark profile complete.
     */
    public function completeProfile(Account $account, array $data): SupplierProfile
    {
        $profile = $this->saveDraft($account, $data);

        $this->assertComplete($profile, $account, $data);

        $profile->update(['profile_completed_at' => now()]);

        return $profile;
    }

    private function assertComplete(SupplierProfile $profile, Account $account, array $data = []): void
    {
        $errors = [];

        $hasSupplierTypes = $account->supplierTypes()->exists() || ! empty($data['supplier_type_ids']);
        if (! $hasSupplierTypes) {
            $errors['supplier_type_ids'] = 'Please select at least one business type.';
        }

        if (blank($profile->display_name))   $errors['display_name']   = 'Display name is required.';
        if (blank($profile->legal_name))     $errors['legal_name']     = 'Legal name is required.';
        if (blank($profile->contact_person)) $errors['contact_person'] = 'Contact person is required.';
        if (blank($profile->contact_email))  $errors['contact_email']  = 'Contact email is required.';
        if (blank($profile->country_id))     $errors['country_id']     = 'Country is required.';
        if (blank($profile->address))        $errors['address']        = 'Address is required.';

        if (count($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function handleFileUpload(array $data, string $key, string $directory, ?string $existing): ?string
    {
        $file = $data[$key] ?? null;

        if (! $file || ! is_object($file)) {
            return $existing;
        }

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $img     = $manager->read($file->getRealPath());

            if ($key === 'logo' || $key === 'profile_photo') {
                $img->cover(512, 512);
            } else {
                $img->cover(1200, 400); // banner ratio
            }

            $ext     = strtolower($file->getClientOriginalExtension());
            $ext     = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';
            $path    = $directory . '/' . Str::random(40) . '.' . $ext;

            $encoded = match ($ext) {
                'png'   => $img->toPng(),
                'webp'  => $img->toWebp(),
                default => $img->toJpeg(85),
            };

            Storage::disk('public')->put($path, $encoded->toString());

            return $path;
        } catch (\Throwable) {
            return $existing;
        }
    }
}
