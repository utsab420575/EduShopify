<?php

namespace App\Http\Requests\Backend\Admin\AccessControl;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.access_control.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:150'],
            'capability_scope' => ['required', 'in:platform,buyer,supplier,common'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
