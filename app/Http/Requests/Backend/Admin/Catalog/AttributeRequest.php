<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class AttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.attributes.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'input_type' => ['required', 'in:text,textarea,number,select,multi_select,boolean,date,color'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'placeholder' => ['nullable', 'string', 'max:150'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_variant' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'values' => ['nullable', 'string'],
        ];
    }
}
