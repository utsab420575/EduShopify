<?php

namespace App\Http\Requests\Backend\Admin\Billing;

use App\Models\SubscriptionBenefit;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Plan Benefit Assignment: benefits[<benefit id>][assigned] (0/1) and
 * benefits[<benefit id>][value] for every benefit shown on the page. Each
 * assigned value is validated against its benefit's own type.
 */
class PlanBenefitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.subscriptions.manage');
    }

    public function rules(): array
    {
        $rules = [
            'benefits' => ['present', 'array'],
            'benefits.*.assigned' => ['nullable', 'boolean'],
        ];

        $benefits = SubscriptionBenefit::whereIn('id', array_keys((array) $this->input('benefits', [])))->get()->keyBy('id');

        foreach ((array) $this->input('benefits', []) as $id => $row) {
            $benefit = $benefits->get((int) $id);
            if (! $benefit) {
                $rules["benefits.{$id}"] = ['prohibited'];

                continue;
            }
            if (filter_var($row['assigned'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $rules["benefits.{$id}.value"] = SubscriptionBenefit::valueRules($benefit->value_type);
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $names = SubscriptionBenefit::whereIn('id', array_keys((array) $this->input('benefits', [])))->pluck('name', 'id');

        return $names->mapWithKeys(fn ($name, $id) => ["benefits.{$id}.value" => "\"{$name}\" value"])->all();
    }
}
