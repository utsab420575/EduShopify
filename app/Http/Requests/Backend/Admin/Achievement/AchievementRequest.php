<?php

namespace App\Http\Requests\Backend\Admin\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class AchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.achievements.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge_icon' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:100'],
            'requirement_value' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
