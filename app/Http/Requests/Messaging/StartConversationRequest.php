<?php

namespace App\Http\Requests\Messaging;

use Illuminate\Foundation\Http\FormRequest;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'context_type'         => ['nullable', 'string', 'in:listing,rfq,quotation,purchase_order,general,support'],
            'context_id'           => ['nullable', 'integer'],
            'initial_message'      => ['nullable', 'string', 'max:5000'],
        ];
    }
}
