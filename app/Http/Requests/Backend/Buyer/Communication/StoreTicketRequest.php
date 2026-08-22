<?php

namespace App\Http\Requests\Backend\Buyer\Communication;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'related_type' => ['nullable', 'in:rfq,quotation,award,purchase_order'],
            'related_id' => ['nullable', 'integer'],
        ];
    }
}
