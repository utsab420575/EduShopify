<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreServiceRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'services';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,active,inactive',
            'icon_id' => 'nullable|exists:icons,id',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a service title.',
        ];
    }
}
