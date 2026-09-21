<?php

namespace App\Http\Requests\Backend\Supplier\Procurement;

/**
 * Backs the per-step wizard autosave endpoints — same shape as
 * SaveQuotationRequest, just relaxed on item_name/quantity/unit_price so a
 * supplier still mid-Step-1 (not every item priced yet) can autosave
 * without a hard validation failure. The real "Save Draft"/"Submit"
 * actions still go through the strict parent SaveQuotationRequest, so a
 * quotation can never actually be finalized while incomplete.
 */
class QuotationAutosaveRequest extends SaveQuotationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['items.*.item_name'] = ['nullable', 'string', 'max:255'];
        $rules['items.*.quantity'] = ['nullable', 'numeric', 'min:0.001'];
        $rules['items.*.unit_price'] = ['nullable', 'numeric', 'min:0'];

        return $rules;
    }
}
