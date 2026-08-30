<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisibilityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.attributes.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('visibility_type')?->id;

        return [
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('visibility_types', 'code')->ignore($id)],
            'engine_type'   => ['required', 'in:invited,open'],
            'max_suppliers' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'     => $this->boolean('is_active', true),
            'sort_order'    => $this->input('sort_order') !== null && $this->input('sort_order') !== '' ? (int)$this->input('sort_order') : 0,
            'max_suppliers' => $this->input('max_suppliers') !== null && $this->input('max_suppliers') !== '' ? (int)$this->input('max_suppliers') : null,
        ]);
    }
}
