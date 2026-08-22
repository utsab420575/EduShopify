<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.attributes.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20'],
            'unit_type' => ['required', 'in:count,weight,volume,length,area,time,other'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
