<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class UpdateGalleryRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'gallery';
    }

    public function rules(): array
    {
        return [
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ];
    }
}
