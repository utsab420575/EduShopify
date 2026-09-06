<?php

namespace App\Http\Requests\FrontendNew;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates only the SHAPE of the client's comparison payload — never
 * localStorage's authoritative claim about price/supplier/attributes/etc.
 * (there isn't any; only ids are ever stored). No `exists:` rule on
 * listing_id/variant_id: an id that no longer resolves through
 * PublicListingQuery::base() is a normal, expected condition (unpublished
 * since being added) that ProductComparisonService::resolve() reports via
 * removed_ids rather than a hard validation failure.
 *
 * Mirrors App\Http\Requests\Frontend\CompareDataRequest — kept as its own
 * class per the v2 rule of not depending on the legacy Frontend namespace.
 */
class CompareDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint — no auth required to compare.
    }

    public function rules(): array
    {
        return [
            'items' => ['present', 'array', 'max:'.(int) config('comparison.max_items', 5)],
            'items.*.listing_id' => ['required', 'integer', 'min:1'],
            'items.*.variant_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['items' => array_values($this->input('items', []))]);
    }
}
