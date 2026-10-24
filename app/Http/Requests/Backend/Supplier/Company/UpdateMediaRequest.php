<?php

namespace App\Http\Requests\Backend\Supplier\Company;

use App\Models\SupplierProfile;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'media';
    }

    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|max:2048',
            // The browser shrinks the picked banner to ≤3000px before upload,
            // so the original arriving here is normally 1–2 MB.
            'banner' => 'nullable|image|max:4096',
            // Crop box in the original's pixels (Cropper.js getData()); also
            // sent alone to re-crop the stored original.
            'banner_crop' => 'nullable|array',
            'banner_crop.x' => 'required_with:banner_crop|numeric|min:0',
            'banner_crop.y' => 'required_with:banner_crop|numeric|min:0',
            'banner_crop.width' => 'required_with:banner_crop|numeric|min:1',
            'banner_crop.height' => 'required_with:banner_crop|numeric|min:1',
            'banner_fit' => ['nullable', Rule::in(SupplierProfile::BANNER_FITS)],
            'profile_photo' => 'nullable|image|max:2048',
        ];
    }
}
