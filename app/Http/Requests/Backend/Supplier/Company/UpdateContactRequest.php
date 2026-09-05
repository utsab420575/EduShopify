<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class UpdateContactRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'contact';
    }

    public function rules(): array
    {
        return [
            'contact_person' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'contact_person.required' => 'Please enter a contact person.',
            'contact_email.required' => 'Please enter a contact email.',
            'country_id.required' => 'Please select a country.',
            'address.required' => 'Please enter an address.',
        ];
    }
}
