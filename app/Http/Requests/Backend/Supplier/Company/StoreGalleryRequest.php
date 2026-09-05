<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreGalleryRequest extends ProfileSectionFormRequest
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
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:5120',
        ];
    }
}
