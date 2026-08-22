<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.categories.manage');
    }

    public function rules(): array
    {
        return [
            'attribute_id'  => ['sometimes', 'required', 'exists:attributes,id'],
            'is_required'   => ['sometimes', 'boolean'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_variant'    => ['sometimes', 'boolean'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
        ];
    }
}
