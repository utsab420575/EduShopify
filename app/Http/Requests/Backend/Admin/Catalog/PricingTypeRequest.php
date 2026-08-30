<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use App\Models\PricingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PricingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.attributes.manage') ?? false;
    }

    public function rules(): array
    {
        $pricingType   = $this->route('pricing_type') ?? $this->route('pricingType');
        $pricingTypeId = $pricingType instanceof PricingType ? $pricingType->id : $pricingType;

        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('pricing_types', 'code')->ignore($pricingTypeId)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
