<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class SupplierTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.categories.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
