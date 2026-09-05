<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class JoinExhibitionRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'exhibitions';
    }

    public function rules(): array
    {
        return [
            'booth_number' => 'nullable|string|max:100',
            'participation_year' => 'nullable|integer|min:2000|max:2100',
        ];
    }
}
