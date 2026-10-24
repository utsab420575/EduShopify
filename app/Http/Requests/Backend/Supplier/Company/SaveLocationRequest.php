<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class SaveLocationRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'locations';
    }

    public function rules(): array
    {
        return [
            'location_type' => ['required', 'in:head_office,registered_office,branch,warehouse,showroom,billing,delivery'],
            'label' => ['nullable', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'Please select a country.',
            'country_id.exists' => 'The selected country is invalid.',
            'address_line_1.required' => 'Please provide the street address.',
            'location_type.required' => 'Please select a location type.',
        ];
    }
}
