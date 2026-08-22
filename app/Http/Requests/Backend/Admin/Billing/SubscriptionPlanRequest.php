<?php

namespace App\Http\Requests\Backend\Admin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('platform.subscriptions.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'billing_type' => ['required', 'in:free,monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'bonus_days' => ['nullable', 'integer', 'min:0'],
            'max_active_listings' => ['nullable', 'integer', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:0'],
            'max_services' => ['nullable', 'integer', 'min:0'],
            'max_team_members' => ['nullable', 'integer', 'min:0'],
            'max_monthly_quotations' => ['nullable', 'integer', 'min:0'],
            'rfq_delay_minutes' => ['nullable', 'integer', 'min:0'],
            'has_rfq_notifications' => ['sometimes', 'boolean'],
            'has_analytics' => ['sometimes', 'boolean'],
            'has_verified_badge' => ['sometimes', 'boolean'],
            'has_homepage_placement' => ['sometimes', 'boolean'],
            'has_team_members' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_free' => ['sometimes', 'boolean'],
            'requires_supplier_approval' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
