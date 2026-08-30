<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\CustomAttributeValueDecisionRequest;
use App\Models\Attribute;
use App\Models\AttributeCustomValueReview;
use App\Models\AttributeValue;
use App\Models\ListingAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomAttributeValueController extends Controller
{
    use InteractsWithAdmin;

    /**
     * Promote a supplier's custom value into the official attribute_values
     * dictionary: create (or reuse) the AttributeValue, backfill every
     * listing that used this custom text to point at it instead, and record
     * the decision. Works the same whether the value was previously
     * untouched ("pending") or already "ignored" — that's the
     * reopen-then-promote path, with no separate reopen step needed.
     */
    public function approve(CustomAttributeValueDecisionRequest $request)
    {
        $this->authorize('platform.attributes.manage');

        $attribute = Attribute::findOrFail($request->integer('attribute_id'));
        $customValue = trim($request->input('custom_value'));

        $attributeValue = DB::transaction(function () use ($attribute, $customValue) {
            $slug = Str::slug($customValue);

            $attributeValue = AttributeValue::where('attribute_id', $attribute->id)
                ->where(fn ($q) => $q->where('value', $customValue)->orWhere('slug', $slug))
                ->first();

            if (! $attributeValue) {
                $attributeValue = AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value'        => $customValue,
                    'slug'         => $slug,
                    'sort_order'   => ($attribute->values()->max('sort_order') ?? 0) + 1,
                    'is_active'    => true,
                ]);
            }

            ListingAttributeValue::where('attribute_id', $attribute->id)
                ->whereRaw('TRIM(custom_value) = ?', [$customValue])
                ->update([
                    'attribute_value_id' => $attributeValue->id,
                    'custom_value'       => null,
                ]);

            AttributeCustomValueReview::updateOrCreate(
                ['attribute_id' => $attribute->id, 'custom_value' => $customValue],
                [
                    'status'                       => 'approved',
                    'resulting_attribute_value_id' => $attributeValue->id,
                    'reviewed_by_user_id'          => $this->admin()->id,
                    'reviewed_at'                  => now(),
                ]
            );

            return $attributeValue;
        });

        activity('catalog')->causedBy($this->admin())->performedOn($attributeValue)
            ->withProperties(['attribute_id' => $attribute->id, 'custom_value' => $customValue])
            ->log('Custom attribute value promoted');

        return back()->with('success', "'{$customValue}' is now an official option for '{$attribute->name}'.");
    }

    /**
     * Mark a custom value as reviewed but not promoted. Listings that used it
     * are left completely untouched — this only means "don't make this a
     * standard, selectable option," not "this product data is invalid."
     */
    public function ignore(CustomAttributeValueDecisionRequest $request)
    {
        $this->authorize('platform.attributes.manage');

        $attribute = Attribute::findOrFail($request->integer('attribute_id'));
        $customValue = trim($request->input('custom_value'));

        AttributeCustomValueReview::updateOrCreate(
            ['attribute_id' => $attribute->id, 'custom_value' => $customValue],
            [
                'status'              => 'ignored',
                'reviewed_by_user_id' => $this->admin()->id,
                'reviewed_at'         => now(),
            ]
        );

        activity('catalog')->causedBy($this->admin())->performedOn($attribute)
            ->withProperties(['custom_value' => $customValue])
            ->log('Custom attribute value ignored');

        return back()->with('success', "'{$customValue}' will not be promoted. Existing listings are unaffected.");
    }
}
