<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['platform.attributes.manage', 'platform.settings.manage', 'platform.categories.manage']);
    }

    public function rules(): array
    {
        return [
            'code'           => ['required', 'string', 'size:3', 'uppercase'],
            'name'           => ['required', 'string', 'max:100'],
            'symbol'         => ['required', 'string', 'max:10'],
            'exchange_rate'  => ['required', 'numeric', 'min:0'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
