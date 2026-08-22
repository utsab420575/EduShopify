<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeSuggestion;
use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttributeSuggestionService
{
    public function approve(AttributeSuggestion $suggestion, User $admin): AttributeSuggestion
    {
        if ($suggestion->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending suggestion can be approved.']);
        }

        DB::transaction(function () use ($suggestion, $admin) {
            $inputType = $suggestion->proposed_input_type === 'multiselect' ? 'multi_select' : $suggestion->proposed_input_type;

            $attribute = Attribute::create([
                'name'       => $suggestion->proposed_name,
                'slug'       => $this->uniqueSlug($suggestion->proposed_name),
                'input_type' => $inputType,
                'is_active'  => true,
            ]);

            foreach ((array) ($suggestion->proposed_values ?? []) as $index => $value) {
                if (blank($value)) {
                    continue;
                }

                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value'        => $value,
                    'slug'         => Str::slug($value),
                    'sort_order'   => $index,
                ]);
            }

            if ($suggestion->category_id) {
                $attribute->categories()->attach($suggestion->category_id);
            }

            $suggestion->update([
                'status'                 => 'approved',
                'resulting_attribute_id' => $attribute->id,
                'reviewed_by_user_id'    => $admin->id,
                'reviewed_at'            => now(),
            ]);
        });

        return $suggestion->fresh();
    }

    public function reject(AttributeSuggestion $suggestion, User $admin, string $comment): AttributeSuggestion
    {
        if ($suggestion->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending suggestion can be rejected.']);
        }

        $suggestion->update([
            'status'              => 'rejected',
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at'         => now(),
            'review_comment'      => $comment,
        ]);

        return $suggestion->fresh();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
