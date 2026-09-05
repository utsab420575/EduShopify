<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class UpdateCompanyRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'company';
    }

    public function rules(): array
    {
        return [
            'display_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'company_type' => 'nullable|string|max:100',
            'founded_year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'employees' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:5000',
            'supplier_type_ids' => 'nullable|array',
            'supplier_type_ids.*' => 'exists:supplier_types,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Please enter a display name.',
        ];
    }
}
