<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class UpdateBusinessHoursRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'hours';
    }

    public function rules(): array
    {
        return [
            'days' => 'required|array',
            'days.*.is_open' => 'nullable|boolean',
            'days.*.open_time' => 'nullable|date_format:H:i',
            'days.*.close_time' => 'nullable|date_format:H:i',
        ];
    }
}
