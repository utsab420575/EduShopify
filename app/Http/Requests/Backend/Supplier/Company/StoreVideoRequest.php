<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreVideoRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'gallery';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'video_url' => 'required|url|max:500',
            'caption' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a video title.',
            'video_url.required' => 'Please enter a video URL.',
        ];
    }
}
