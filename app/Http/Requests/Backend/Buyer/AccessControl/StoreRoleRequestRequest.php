<?php

namespace App\Http\Requests\Backend\Buyer\AccessControl;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_name' => ['required', 'string', 'max:255', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:2000'],
            'requested_permissions' => ['required', 'array', 'min:1'],
            'requested_permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
