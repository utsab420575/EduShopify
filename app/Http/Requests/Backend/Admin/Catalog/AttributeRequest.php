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
        $attribute = $this->route('attribute');
        $attributeId = $attribute instanceof \App\Models\Attribute ? $attribute->id : $attribute;

        return [
            'attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'name'               => ['required', 'string', 'max:150'],
            'slug'               => ['nullable', 'string', 'max:150', \Illuminate\Validation\Rule::unique('attributes', 'slug')->ignore($attributeId)],
            'input_type'         => ['required', 'in:text,textarea,number,select,multi_select,boolean,date,color'],
            'unit_id'            => ['nullable', 'exists:units,id'],
            'placeholder'        => ['nullable', 'string', 'max:150'],
            'is_filterable'      => ['sometimes', 'boolean'],
            'is_variant'         => ['sometimes', 'boolean'],
            'is_required'        => ['sometimes', 'boolean'],
            'is_active'          => ['sometimes', 'boolean'],
            'sort_order'         => ['nullable', 'integer', 'min:0'],
            // Predefined values
            'values'             => ['nullable'],
            'values.*.id'        => ['nullable', 'integer'],
            'values.*.value'     => ['nullable', 'string', 'max:255'],
            'values.*.slug'      => ['nullable', 'string', 'max:255'],
            'values.*.color_hex' => ['nullable', 'string', 'max:20'],
            'values.*.sort_order'=> ['nullable', 'integer'],
            'values.*.is_active' => ['nullable', 'boolean'],
            // Validation helper fields
            'min_value'          => ['nullable', 'numeric'],
            'max_value'          => ['nullable', 'numeric'],
            'min_length'         => ['nullable', 'integer', 'min:0'],
            'max_length'         => ['nullable', 'integer', 'min:0'],
            'decimal_allowed'    => ['sometimes', 'boolean'],
        ];
    }
}
