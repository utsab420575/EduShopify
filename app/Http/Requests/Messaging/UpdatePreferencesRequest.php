<?php

namespace App\Http\Requests\Messaging;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sound_enabled'                 => ['required', 'boolean'],
            'browser_notifications_enabled' => ['required', 'boolean'],
            'unread_email_enabled'          => ['required', 'boolean'],
            'unread_email_delay_hours'      => ['nullable', 'integer', 'min:1', 'max:168'],
        ];
    }
}
