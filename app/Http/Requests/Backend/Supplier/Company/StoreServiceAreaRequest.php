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

    protected function prepareForValidation(): void
    {
        $countryId = $this->input('country_id');

        if ($countryId === 'global') {
            $this->merge([
                'state_id' => null,
                'city_id' => null,
                'radius_km' => null,
            ]);
            return;
        }

        $stateId = $this->input('state_id');
        $cityId = $this->input('city_id');
        $radiusKm = $this->input('radius_km');

        $this->merge([
            'state_id' => ($stateId === '0' || $stateId === 0 || $stateId === '' || $stateId === null) ? null : $stateId,
            'city_id' => ($cityId === '0' || $cityId === 0 || $cityId === '' || $cityId === null) ? null : $cityId,
            'radius_km' => ($radiusKm === '' || $radiusKm === null) ? null : $radiusKm,
        ]);
    }

    public function rules(): array
    {
        return [
            'country_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'global' && ! \App\Models\Country::where('id', $value)->exists()) {
                        $fail('Please select a valid country or Worldwide.');
                    }
                },
            ],
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
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
