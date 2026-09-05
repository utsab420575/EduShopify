<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreServiceAreaRequest extends ProfileSectionFormRequest
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
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'radius_km' => 'nullable|numeric|min:0',
            'is_primary' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'Please select a country.',
        ];
    }
}
