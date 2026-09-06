<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.categories.manage');
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');
        $parentId = $this->filled('parent_id') ? $this->input('parent_id') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('parent_id', $parentId)->whereNull('deleted_at'))
                    ->ignore($categoryId),
            ],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', 'in:product,service,both'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists under the selected parent category.',
        ];
    }
}
