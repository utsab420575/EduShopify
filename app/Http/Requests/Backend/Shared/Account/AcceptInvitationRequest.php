<?php

namespace App\Http\Requests\Backend\Shared\Account;

use Illuminate\Foundation\Http\FormRequest;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Name/password are only required when no matching user account exists
        // yet — an existing user just confirms while logged in.
        $requiresRegistration = $this->boolean('requires_registration');

        return [
            'name' => [$requiresRegistration ? 'required' : 'nullable', 'string', 'max:150'],
            'password' => [$requiresRegistration ? 'required' : 'nullable', 'confirmed', 'min:8'],
        ];
    }
}
