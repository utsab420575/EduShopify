<?php

namespace App\Http\Requests\Backend\Buyer\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class CompareQuotationsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_ids'   => ['required', 'array', 'max:'.config('quotation_comparison.max_items', 5)],
            'quotation_ids.*' => ['integer', 'min:1'],
        ];
    }
}
