<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.attributes.manage');
    }

    public function rules(): array
    {
        $group = $this->route('attribute_group');
        $groupId = $group instanceof \App\Models\AttributeGroup ? $group->id : $group;

        return [
            'name'        => ['required', 'string', 'max:150'],
            'slug'        => ['nullable', 'string', 'max:150', Rule::unique('attribute_groups', 'slug')->ignore($groupId)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
