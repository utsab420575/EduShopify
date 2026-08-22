<?php

namespace App\Http\Requests\Backend\Buyer\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'max:2048'],
            'buyer_type_ids' => ['nullable', 'array'],
            'buyer_type_ids.*' => ['integer', 'exists:buyer_types,id'],
            'display_name' => ['required', 'string', 'max:200'],
            'organization_name' => ['nullable', 'string', 'max:200'],
            'contact_person' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['required', 'string', 'max:1000'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'procurement_info' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
