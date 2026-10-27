<?php

namespace App\Http\Requests\Backend\Admin\Achievement;

use App\Support\BadgeColor;
use Illuminate\Foundation\Http\FormRequest;

class AchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.achievements.manage');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->badge_color)) {
            $this->merge(['badge_color' => strtolower(trim($this->badge_color))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge_icon' => ['nullable', 'string', 'max:150'],
            'display_badge' => ['nullable', 'string', 'max:60'],
            'badge_color' => ['nullable', 'regex:'.BadgeColor::HEX_PATTERN],
            'type' => ['nullable', 'string', 'max:100'],
            'requirement_value' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
