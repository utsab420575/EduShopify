<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use App\Models\InputType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InputTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.attributes.manage') ?? false;
    }

    public function rules(): array
    {
        $inputType = $this->route('input_type') ?? $this->route('inputType');
        $inputTypeId = $inputType instanceof InputType ? $inputType->id : $inputType;

        return [
            'name'         => ['required', 'string', 'max:100'],
            'code'         => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('input_types', 'code')->ignore($inputTypeId)],
            'description'  => ['nullable', 'string', 'max:255'],
            'has_options'  => ['boolean'],
            'is_multiple'  => ['boolean'],
            'is_active'    => ['boolean'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ];
    }
}
