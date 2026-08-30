<?php

namespace App\Http\Requests\Backend\Admin\Catalog;

use App\Models\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.attributes.manage') ?? false;
    }

    public function rules(): array
    {
        $listingType   = $this->route('listing_type') ?? $this->route('listingType');
        $listingTypeId = $listingType instanceof ListingType ? $listingType->id : $listingType;

        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('listing_types', 'code')->ignore($listingTypeId)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
