<?php

namespace App\Http\Requests\Backend\Supplier\Procurement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by QuotationRevisionController::store — the buyer-requested (or
 * supplier-initiated) revision of an already-submitted quotation. Same item
 * shape as SaveQuotationRequest, plus a required change_summary so the
 * revision history stays meaningful (shown to the buyer and preserved on
 * the quotation_revisions snapshot).
 */
class ReviseQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:quotation_items,id'],
            'items.*.rfq_item_id' => ['nullable', 'integer', 'exists:rfq_items,id'],
            'items.*.offered_listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'items.*.offered_variant_id' => ['nullable', 'integer', 'exists:listing_variants,id'],
            'items.*.is_alternative' => ['nullable', 'boolean'],
            'items.*.is_optional_addon' => ['nullable', 'boolean'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'items.*.custom_unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'items.*.specs' => ['nullable', 'array'],
            'items.*.attribute_values' => ['nullable', 'array'],
            'items.*.attribute_values.*.attribute_value_id' => ['nullable'],
            'items.*.attribute_values.*.custom_value' => ['nullable', 'string', 'max:255'],
            'items.*.attribute_values.*.value_text' => ['nullable', 'string'],
            'items.*.attribute_values.*.value_number' => ['nullable', 'numeric'],
            'items.*.attribute_values.*.value_boolean' => ['nullable', 'boolean'],
            'items.*.attribute_values.*.value_date' => ['nullable', 'date'],
            'items.*.attribute_values.*.value_json' => ['nullable'],

            'currency_code' => ['nullable', 'string', 'size:3'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:1000'],
            'support_terms' => ['nullable', 'string', 'max:1000'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'proposal' => ['nullable', 'string', 'max:5000'],
            'change_summary' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['items' => array_values(array_merge($this->input('items', []), $this->input('addons', [])))]);
    }
}
