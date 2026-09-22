<?php

namespace App\Http\Requests\Backend\Supplier\Procurement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by QuotationController::store/update — always a draft-save. A draft
 * is explicitly allowed to be incomplete (spec §24), so items is optional
 * here; completeness (partial-quotation/alternative-product rules, at least
 * one item) is enforced only at submit time by QuotationService::submitDraft().
 */
class SaveQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller-level policy authorization handles this.
    }

    public function rules(): array
    {
        return [
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:quotation_items,id'],
            'items.*.client_ref' => ['nullable', 'string', 'max:64'],
            'items.*.response_method' => ['nullable', 'in:marketplace,custom,copy_spec,document'],
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
            'items.*.custom_attributes' => ['nullable', 'array'],
            'items.*.offers' => ['nullable', 'array'],
            'items.*.offers.*.id' => ['nullable', 'integer'],
            'items.*.offers.*.offer_method' => ['nullable', 'in:marketplace,custom,document,copy_spec'],
            'items.*.offers.*.marketplace_product_id' => ['nullable', 'integer', 'exists:listings,id'],
            'items.*.offers.*.offered_variant_id' => ['nullable', 'integer', 'exists:listing_variants,id'],
            'items.*.offers.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.offers.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.offers.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.offers.*.specifications' => ['nullable'],
            'items.*.offers.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.offers.*.unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'items.*.offers.*.custom_unit' => ['nullable', 'string', 'max:50'],
            'items.*.offers.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.offers.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.offers.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.offers.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.offers.*.delivery_time' => ['nullable', 'integer', 'min:0'],
            'items.*.offers.*.status' => ['nullable', 'in:draft,submitted,withdrawn'],
            'items.*.offers.*.is_primary' => ['nullable', 'boolean'],
            'items.*.offers.*.is_selected' => ['nullable', 'boolean'],
            'items.*.offers.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'items.*.attribute_values' => ['nullable', 'array'],
            'items.*.attribute_values.*.attribute_value_id' => ['nullable'],
            'items.*.attribute_values.*.custom_value' => ['nullable', 'string', 'max:255'],
            'items.*.attribute_values.*.value_text' => ['nullable', 'string'],
            'items.*.attribute_values.*.value_number' => ['nullable', 'numeric'],
            'items.*.attribute_values.*.value_boolean' => ['nullable', 'boolean'],
            'items.*.attribute_values.*.value_date' => ['nullable', 'date'],
            'items.*.attribute_values.*.value_json' => ['nullable'],

            'current_step' => ['nullable', 'integer', 'min:1', 'max:3'],
            'max_completed_step' => ['nullable', 'integer', 'min:1', 'max:3'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:1000'],
            'support_terms' => ['nullable', 'string', 'max:1000'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'proposal' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * The form keeps RFQ-item responses and optional add-ons as two separate
     * Alpine lists (addons must never be mistaken for RFQ responses in the
     * UI), but the backend syncs them as one flat quotation_items array.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['items' => array_values(array_merge($this->input('items', []), $this->input('addons', [])))]);
    }
}
