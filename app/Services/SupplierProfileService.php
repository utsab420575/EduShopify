<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SupplierProfile;
use Illuminate\Http\UploadedFile;
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

        // A banner replaced through this path (onboarding wizard) has no
        // original/crop of its own — drop the previous ones so a later
        // re-crop doesn't open a stale original.
        if ($bannerPath !== $profile->banner && $profile->banner_original) {
            if (str_starts_with($profile->banner_original, 'supplier-banners/') && $profile->banner_original !== $bannerPath) {
                Storage::disk('public')->delete($profile->banner_original);
            }
            $profile->banner_original = null;
            $profile->banner_crop = null;
        }

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
            'description'       => $data['description']       ?? $profile->description,
            'logo'              => $logoPath                  ?? $profile->logo,
            'banner'            => $bannerPath                ?? $profile->banner,
            'profile_photo'     => $photoPath                 ?? $profile->profile_photo,
        ], fn ($v) => $v !== null));

        $profile->save();

        if (! empty($data['country_id']) || ! empty($data['address'])) {
            $primaryLoc = $account->locations()->where('is_primary', true)->first();
            if ($primaryLoc) {
                $primaryLoc->update(array_filter([
                    'country_id'     => $data['country_id'] ?? $primaryLoc->country_id,
                    'state_id'       => $data['state_id'] ?? $primaryLoc->state_id,
                    'city_id'        => $data['city_id'] ?? $primaryLoc->city_id,
                    'address_line_1' => $data['address'] ?? $primaryLoc->address_line_1,
                ], fn ($v) => $v !== null));
            } else {
                $account->locations()->create([
                    'location_type'       => 'head_office',
                    'label'               => 'Head Office',
                    'country_id'          => $data['country_id'] ?? null,
                    'state_id'            => $data['state_id'] ?? null,
                    'city_id'             => $data['city_id'] ?? null,
                    'address_line_1'      => $data['address'] ?? 'Head Office',
                    'is_primary'          => true,
                    'is_active'           => true,
                    'created_by_user_id'  => \Illuminate\Support\Facades\Auth::id() ?? $account->primary_owner_user_id,
                ]);
            }
        }

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

        $primaryLocation = $account->primaryLocation ?? $account->locations()->first();
        if (! $primaryLocation || blank($primaryLocation->country_id)) {
            $errors['country_id'] = 'Country is required on your primary location.';
        }
        if (! $primaryLocation || blank($primaryLocation->address_line_1)) {
            $errors['address'] = 'Address is required on your primary location.';
        }

        if (count($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Hero banner, cropped server-side from a stored original so it can be
     * re-cropped later without re-uploading.
     *
     *  - $file: a new original (the browser has already shrunk it to ≤3000px);
     *    null re-crops the stored original instead.
     *  - $crop: {x, y, width, height} in the original's pixels, straight from
     *    Cropper.js getData() — untrusted, so clamped to the image here.
     *
     * Both files are stored as JPEG q85 under supplier-banners/dd_month_year/.
     * `banner` stays the cropped image, so every page that displays it is
     * unaffected.
     */
    public function updateBanner(Account $account, ?UploadedFile $file, ?array $crop, ?string $fit = null): SupplierProfile
    {
        /** @var SupplierProfile $profile */
        $profile = $account->supplierProfile ?? SupplierProfile::create(['account_id' => $account->id]);

        // How the banner fills the hero box — independent of the image, so
        // it can change on its own without a re-crop.
        if ($fit && in_array($fit, SupplierProfile::BANNER_FITS, true) && $fit !== $profile->banner_fit) {
            $profile->update(['banner_fit' => $fit]);
        }

        if (! $file && ! $crop) {
            return $profile;
        }

        $disk = Storage::disk('public');
        $directory = 'supplier-banners/' . strtolower(now()->format('d_F_Y'));
        $newOriginal = null;
        $croppedPath = null;

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            if ($file) {
                $image = $manager->read($file->getRealPath())->scaleDown(width: 3000, height: 3000);
                $newOriginal = $directory . '/' . Str::random(40) . '.jpg';
                $disk->put($newOriginal, $image->toJpeg(85)->toString());
                $originalPath = $newOriginal;
            } else {
                // A banner saved before originals were kept is its own source.
                $originalPath = $profile->banner_original ?: $profile->banner;
                if (! $originalPath || ! $disk->exists($originalPath)) {
                    throw ValidationException::withMessages(['banner' => 'There is no banner to re-crop. Please upload an image.']);
                }
                $image = $manager->read($disk->path($originalPath));
            }

            $crop = $this->clampCrop($crop, $image->width(), $image->height());

            // Same 1760px display width / 1200px height ceiling as before.
            $image->crop($crop['width'], $crop['height'], $crop['x'], $crop['y'])
                ->scaleDown(width: 1760, height: 1200);
            $croppedPath = $directory . '/' . Str::random(40) . '.jpg';
            $disk->put($croppedPath, $image->toJpeg(85)->toString());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            foreach (array_filter([$newOriginal, $croppedPath]) as $partial) {
                $disk->delete($partial);
            }

            throw ValidationException::withMessages([
                'banner' => 'This image could not be processed. Please try a different JPG, PNG or WebP file.',
            ]);
        }

        $replaced = [$profile->banner, $profile->banner_original];

        $profile->update([
            'banner' => $croppedPath,
            'banner_original' => $originalPath,
            'banner_crop' => $crop,
        ]);

        // Delete what this replaced — never the file now serving as the
        // original (a pre-existing banner re-cropped for the first time).
        foreach (array_unique(array_filter($replaced)) as $old) {
            if ($old !== $originalPath && $old !== $croppedPath && str_starts_with($old, 'supplier-banners/')) {
                $disk->delete($old);
            }
        }

        return $profile;
    }

    /**
     * Clamps a client-sent crop box into the image. With no box, defaults to
     * the hero's 22:5 shape across the full width, vertically centred.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    private function clampCrop(?array $crop, int $imageWidth, int $imageHeight): array
    {
        if (! $crop) {
            $height = min($imageHeight, (int) round($imageWidth * 5 / 22));

            return ['x' => 0, 'y' => intdiv($imageHeight - $height, 2), 'width' => $imageWidth, 'height' => $height];
        }

        $x = max(0, min((int) round($crop['x']), $imageWidth - 1));
        $y = max(0, min((int) round($crop['y']), $imageHeight - 1));

        return [
            'x' => $x,
            'y' => $y,
            'width' => max(1, min((int) round($crop['width']), $imageWidth - $x)),
            'height' => max(1, min((int) round($crop['height']), $imageHeight - $y)),
        ];
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
                // Width matches the public supplier profile's .hero-banner
                // box (frontend_new.css: max-w-7xl container, 1232px content
                // width); height is intentionally NOT forced — the banner
                // upload UI (Cropper.js) lets the supplier pick a taller or
                // shorter crop than the hero's 280px display height, which
                // .hero-banner img's own object-fit:cover then center-crops
                // to fit at render time, same as any other image there.
                // scaleDown only shrinks (never crops/upscales), preserving
                // whatever composition the client already cropped to; the
                // 1200 height cap is just a sanity ceiling against abuse.
                $img->scaleDown(width: 1760, height: 1200);
            }

            $ext     = strtolower($file->getClientOriginalExtension());
            $ext     = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';
            // e.g. supplier-logos/25_october_2026/<random>.jpg
            $path    = $directory . '/' . strtolower(now()->format('d_F_Y')) . '/' . Str::random(40) . '.' . $ext;

            $encoded = match ($ext) {
                'png'   => $img->toPng(),
                'webp'  => $img->toWebp(),
                default => $img->toJpeg(85),
            };

            Storage::disk('public')->put($path, $encoded->toString());

            // Only once the new file is safely stored: remove the one it
            // replaces, so a failed upload never leaves the profile imageless.
            // Restricted to this field's own directory so nothing else on the
            // public disk can be deleted through a stale/odd stored path.
            if ($existing && $existing !== $path && str_starts_with($existing, $directory . '/')) {
                Storage::disk('public')->delete($existing);
            }

            return $path;
        } catch (\Throwable $e) {
            // Previously this silently kept the old image while the page
            // still reported "saved" — tell the user the new one failed.
            report($e);

            throw ValidationException::withMessages([
                $key => 'This image could not be processed. Please try a different JPG, PNG or WebP file.',
            ]);
        }
    }
}
