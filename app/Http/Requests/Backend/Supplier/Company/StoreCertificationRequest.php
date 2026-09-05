<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreCertificationRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'achievements';
    }

    public function rules(): array
    {
        return [
            'certification_name' => 'required|string|max:255',
            'certification_title' => 'required|string|max:255',
            'certification_description' => 'required|string|max:5000',
            'certification_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:certification_date',
            'file' => 'nullable|file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'certification_name.required' => 'Please enter the certification name.',
            'certification_title.required' => 'Please enter the certification title.',
            'certification_description.required' => 'Please describe this certification.',
        ];
    }
}
