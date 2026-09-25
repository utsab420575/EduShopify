<?php

namespace App\Http\Requests\Backend\Supplier\Company;

use App\Models\Account;
use Illuminate\Validation\Rule;

class UpdateSocialLinkRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'social-links';
    }

    public function rules(): array
    {
        $account = $this->user()?->account;
        $link = $this->route('socialLink');

        return [
            'social_platform_id' => [
                'required',
                'exists:social_platforms,id',
                Rule::unique('social_links', 'social_platform_id')
                    ->where('socialable_type', Account::class)
                    ->where('socialable_id', $account?->id ?? 0)
                    ->ignore($link?->id),
            ],
            'url' => 'required|url|max:500',
            'handle' => 'nullable|string|max:150',
            'label' => 'nullable|string|max:150',
            'is_public' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'social_platform_id.required' => 'Please select a social platform.',
            'social_platform_id.unique' => 'A link for this platform has already been added.',
            'url.required' => 'Please enter the URL for this social link.',
            'url.url' => 'Please enter a valid web URL (e.g. https://...).',
        ];
    }
}
