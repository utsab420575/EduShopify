<?php

namespace App\Http\Requests\Backend\Buyer\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class ExtendRfqDeadlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deadline_type' => ['required', 'in:quotation,qna'],
            'new_deadline' => ['required', 'date', 'after:now'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
