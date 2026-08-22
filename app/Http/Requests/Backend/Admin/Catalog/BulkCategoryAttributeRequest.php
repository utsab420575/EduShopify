<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class BulkCategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.categories.manage');
    }

    public function rules(): array
    {
        return [
            'attribute_ids'   => ['required', 'array', 'min:1'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'attribute_ids.required' => 'Please select at least one attribute to assign.',
            'attribute_ids.min'      => 'Please select at least one attribute to assign.',
        ];
    }
}
