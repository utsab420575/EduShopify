<?php

namespace App\Http\Requests\Backend\Admin\Billing;

use App\Models\SubscriptionBenefit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create/edit a benefit. Once a benefit is in use (assigned to a plan or an
 * override), its code and value type are locked: snapshots and the resolver
 * look benefits up by code, and stored values are typed by value_type.
 */
class SubscriptionBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.subscriptions.manage');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->code)) {
            $this->merge(['code' => strtolower(trim($this->code))]);
        }
    }

    public function rules(): array
    {
        /** @var SubscriptionBenefit|null $benefit */
        $benefit = $this->route('benefit');
        $locked = $benefit?->exists && $benefit->isInUse();
        $type = $locked ? $benefit->value_type : (string) $this->input('value_type');

        return [
            'code' => array_merge(
                ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('subscription_benefits', 'code')->ignore($benefit?->id)],
                $locked ? [Rule::in([$benefit->code])] : []
            ),
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'value_type' => array_merge(
                ['required', Rule::in(array_keys(SubscriptionBenefit::VALUE_TYPES))],
                $locked ? [Rule::in([$benefit->value_type])] : []
            ),
            'default_value' => array_key_exists($type, SubscriptionBenefit::VALUE_TYPES)
                ? SubscriptionBenefit::valueRules($type, required: false)
                : ['nullable'],
            'unit' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Use lowercase letters, numbers and underscores, starting with a letter (e.g. featured_supplier).',
            'code.in' => 'The code cannot change while the benefit is assigned to plans or overrides.',
            'value_type.in' => 'The value type cannot change while the benefit is assigned to plans or overrides.',
        ];
    }

    /** Validated data with default_value cast to the benefit's type. */
    public function benefitData(): array
    {
        $data = $this->validated();
        $data['default_value'] = ($data['default_value'] ?? null) === null || $data['default_value'] === ''
            ? null
            : SubscriptionBenefit::normalizeValue($data['value_type'], $data['default_value']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
