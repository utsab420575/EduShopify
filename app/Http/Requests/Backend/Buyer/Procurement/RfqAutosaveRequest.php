<?php

namespace App\Http\Requests\Backend\Buyer\Procurement;

/**
 * Backs the per-step wizard autosave endpoints — same shape as
 * SaveRfqRequest (and reuses its prepareForValidation() normalization
 * unchanged), just relaxed on the fields that genuinely aren't filled in
 * yet this early (title/deadline/visibility/suppliers are steps 2 and 3).
 * The real "Save Draft"/"Final Submit" actions on step 4 still go through
 * the strict parent SaveRfqRequest, so nothing can actually publish
 * without those fields.
 */
class RfqAutosaveRequest extends SaveRfqRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['title'] = ['nullable', 'string', 'max:255'];
        $rules['visibility_type_id'] = ['nullable', 'exists:visibility_types,id'];
        $rules['selected_supplier_ids'] = ['nullable', 'array'];
        $rules['quotation_deadline'] = ['nullable', 'date'];

        return $rules;
    }
}
