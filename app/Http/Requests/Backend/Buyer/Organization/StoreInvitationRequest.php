<?php

namespace App\Http\Requests\Backend\Buyer\Organization;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invited_email' => ['required', 'email', 'max:255'],
            'invited_name' => ['nullable', 'string', 'max:150'],
            'invited_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
