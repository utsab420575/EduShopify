<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use App\Models\SalesMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.attributes.manage') ?? false;
    }

    public function rules(): array
    {
        $salesMode   = $this->route('sales_mode') ?? $this->route('salesMode');
        $salesModeId = $salesMode instanceof SalesMode ? $salesMode->id : $salesMode;

        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('sales_modes', 'code')->ignore($salesModeId)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
