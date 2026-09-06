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
        $vt = $this->input('visibility_type_id')
            ? \App\Models\VisibilityType::find($this->input('visibility_type_id'))
            : null;

        $isInvited = $vt ? $vt->isInvited() : in_array($this->input('visibility_type'), ['selected_suppliers', 'direct', 'invited'], true);
        $maxSuppliers = $vt?->max_suppliers;

        $supplierIdRules = ['array'];
        if ($isInvited) {
            $supplierIdRules[] = 'required';
            $supplierIdRules[] = 'min:1';
            if ($maxSuppliers) {
                $supplierIdRules[] = "max:{$maxSuppliers}";
            }
        } else {
            $supplierIdRules[] = 'nullable';
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            'visibility_type_id' => ['required', 'exists:visibility_types,id'],
            'selected_supplier_ids' => $supplierIdRules,
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
            'items.*.attribute_values' => ['nullable', 'array'],
            'items.*.attribute_values.*.attribute_value_id' => ['nullable'],
            'items.*.attribute_values.*.custom_value' => ['nullable', 'string', 'max:255'],
            'items.*.attribute_values.*.value_text' => ['nullable', 'string'],
            'items.*.attribute_values.*.value_number' => ['nullable', 'numeric'],
            'items.*.attribute_values.*.value_boolean' => ['nullable', 'boolean'],
            'items.*.attribute_values.*.value_date' => ['nullable', 'date'],
            'items.*.attribute_values.*.value_json' => ['nullable'],

            'target_filter' => ['nullable', 'array'],
            'target_filter.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'target_filter.location_match_level' => ['nullable', 'in:none,country,state,city'],
            'target_filter.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'target_filter.state_id' => ['nullable', 'integer', 'exists:states,id'],
            'target_filter.city_id' => ['nullable', 'integer', 'exists:cities,id'],

            // Delivery locations beyond the primary delivery_* columns above.
            'additional_addresses' => ['nullable', 'array'],
            'additional_addresses.*.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'additional_addresses.*.state_id' => ['nullable', 'integer', 'exists:states,id'],
            'additional_addresses.*.city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'additional_addresses.*.address' => ['nullable', 'string', 'max:1000'],
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
        $visibilityTypeId = $this->input('visibility_type_id');
        if (! $visibilityTypeId && $this->filled('visibility_type')) {
            $raw = $this->input('visibility_type');
            if (is_numeric($raw)) {
                $visibilityTypeId = (int)$raw;
            } else {
                $code = match ($raw) {
                    'global'             => 'open_matching',
                    'selected_suppliers' => 'invited',
                    default              => $raw,
                };
                $visibilityTypeId = \App\Models\VisibilityType::firstOrCreate(
                    ['code' => $code],
                    [
                        'name'        => ucfirst(str_replace('_', ' ', $code)),
                        'engine_type' => in_array($code, ['direct', 'invited'], true) ? 'invited' : 'open',
                        'is_active'   => true,
                    ]
                )->id;
            }
        }
        if ($visibilityTypeId) {
            $this->merge(['visibility_type_id' => $visibilityTypeId]);
        }

        $this->merge([
            'allow_partial_quotation' => $this->boolean('allow_partial_quotation', true),
            'allow_alternative_products' => $this->boolean('allow_alternative_products', true),
            'items' => array_values($this->input('items', [])),
        ]);

        // The delivery/target-filter location selects use 0 as their
        // "Select country/state/city" placeholder value (so their Alpine
        // state stays numeric), which otherwise submits as a real 0 and
        // fails `exists:...` — these fields are genuinely optional, so
        // normalize the placeholder sentinel to null before validation.
        $locationFields = ['delivery_country_id', 'delivery_state_id', 'delivery_city_id'];
        $normalized = [];
        foreach ($locationFields as $field) {
            if ((int) $this->input($field) === 0) {
                $normalized[$field] = null;
            }
        }
        if ($normalized) {
            $this->merge($normalized);
        }

        $targetFilter = $this->input('target_filter', []);
        if (is_array($targetFilter)) {
            foreach (['country_id', 'state_id', 'city_id'] as $key) {
                if ((int) ($targetFilter[$key] ?? 0) === 0) {
                    $targetFilter[$key] = null;
                }
            }
            $this->merge(['target_filter' => $targetFilter]);
        }

        $additionalAddresses = $this->input('additional_addresses', []);
        if (is_array($additionalAddresses)) {
            foreach ($additionalAddresses as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach (['country_id', 'state_id', 'city_id'] as $key) {
                    if ((int) ($row[$key] ?? 0) === 0) {
                        $additionalAddresses[$i][$key] = null;
                    }
                }
            }
            $this->merge(['additional_addresses' => array_values($additionalAddresses)]);
        }
    }
}
