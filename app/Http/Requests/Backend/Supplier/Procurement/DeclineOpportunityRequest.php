<?php

namespace App\Http\Requests\Backend\Supplier\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class DeclineOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
