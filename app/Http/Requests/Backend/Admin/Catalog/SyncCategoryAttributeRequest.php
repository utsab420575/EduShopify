<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class SyncCategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.categories.manage');
    }

    public function rules(): array
    {
        return [
            'attribute_ids'   => ['sometimes', 'array'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
        ];
    }
}
