<?php

namespace App\Http\Requests\Backend\Supplier\Company;

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
            'banner' => 'nullable|image|max:4096',
            'profile_photo' => 'nullable|image|max:2048',
        ];
    }
}
