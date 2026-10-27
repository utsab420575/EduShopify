<?php

namespace App\Http\Requests\Backend\Admin\Billing;

use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create (POST — benefit chosen) or edit (PUT — benefit fixed) a supplier
 * benefit override. The value is validated against the benefit's type; the
 * window is optional (null start = now, null expiry = never). Overlap with
 * another active override is checked in BenefitOverrideService.
 */
class AccountBenefitOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.subscriptions.manage');
    }

    public function rules(): array
    {
        /** @var AccountBenefitOverride|null $override */
        $override = $this->route('override');
        $benefit = $override?->benefit
            ?? SubscriptionBenefit::active()->find((int) $this->input('subscription_benefit_id'));

        return [
            'subscription_benefit_id' => $override
                ? ['prohibited']
                : ['required', 'integer', Rule::exists('subscription_benefits', 'id')->where('is_active', true)],
            'value' => $benefit ? SubscriptionBenefit::valueRules($benefit->value_type) : ['required'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => array_merge(
                ['nullable', 'date'],
                $this->filled('starts_at') ? ['after:starts_at'] : [],
                $override ? [] : ['after:now']
            ),
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_benefit_id.exists' => 'Choose an active benefit.',
            'expires_at.after' => 'The expiry must be after the start (and in the future for a new override).',
        ];
    }
}
