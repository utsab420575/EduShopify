<?php

namespace App\Http\Requests\Backend\Buyer\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class SaveRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller-level policy authorization handles this.
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            'visibility_type' => ['required', 'in:global,selected_suppliers'],
            'selected_supplier_ids' => ['required_if:visibility_type,selected_suppliers', 'array'],
            'selected_supplier_ids.*' => ['integer', 'exists:accounts,id'],

            'currency_code' => ['nullable', 'string', 'size:3'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],

            'delivery_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'delivery_state_id' => ['nullable', 'integer', 'exists:states,id'],
            'delivery_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'delivery_address' => ['nullable', 'string', 'max:1000'],

            'allow_partial_quotation' => ['nullable', 'boolean'],
            'allow_alternative_products' => ['nullable', 'boolean'],

            'quotation_deadline' => ['required', 'date', 'after:now'],
            'qna_deadline' => ['nullable', 'date', 'before:quotation_deadline'],
            'expected_delivery_date' => ['nullable', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_type' => ['required', 'in:product,service'],
            'items.*.listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'items.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'items.*.custom_unit' => ['nullable', 'string', 'max:50'],
            'items.*.estimated_unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'selected_supplier_ids.required_if' => 'Select at least one supplier to invite.',
            'items.required' => 'Add at least one item to this RFQ.',
            'items.*.item_name.required' => 'Every item needs a name.',
            'items.*.quantity.required' => 'Every item needs a quantity.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_partial_quotation' => $this->boolean('allow_partial_quotation', true),
            'allow_alternative_products' => $this->boolean('allow_alternative_products', true),
            'items' => array_values($this->input('items', [])),
        ]);
    }
}
