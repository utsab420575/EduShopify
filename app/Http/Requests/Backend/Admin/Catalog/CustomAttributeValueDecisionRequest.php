<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CustomAttributeValueDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.attributes.manage');
    }

    public function rules(): array
    {
        return [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'custom_value' => ['required', 'string', 'max:255'],
        ];
    }
}
