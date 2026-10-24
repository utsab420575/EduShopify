<?php

namespace App\Http\Requests\Backend\Supplier\Company;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Drag-and-drop reorder of the Services section: the full list of the
 * supplier's service ids, top to bottom. Ownership is checked in the
 * controller against the current account.
 */
class ReorderServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|distinct',
        ];
    }
}
