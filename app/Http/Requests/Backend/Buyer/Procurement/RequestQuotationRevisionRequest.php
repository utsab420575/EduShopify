<?php

namespace App\Http\Requests\Backend\Buyer\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class RequestQuotationRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_changes' => ['required', 'string', 'max:1000'],
        ];
    }
}
