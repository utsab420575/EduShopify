<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategorySuggestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategorySuggestionService
{
    public function approve(CategorySuggestion $suggestion, User $admin): CategorySuggestion
    {
        if ($suggestion->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending suggestion can be approved.']);
        }

        DB::transaction(function () use ($suggestion, $admin) {
            $category = Category::create([
                'parent_id'             => $suggestion->parent_category_id,
                'name'                  => $suggestion->proposed_name,
                'slug'                  => $this->uniqueSlug($suggestion->proposed_name),
                'description'           => $suggestion->description,
                'type'                  => $suggestion->proposed_type,
                'approval_status'       => 'approved',
                'created_by_account_id' => $suggestion->supplier_account_id,
                'created_by_user_id'    => $suggestion->suggested_by_user_id,
                'reviewed_by_user_id'   => $admin->id,
                'reviewed_at'           => now(),
                'is_active'             => true,
            ]);

            $suggestion->update([
                'status'                => 'approved',
                'resulting_category_id' => $category->id,
                'reviewed_by_user_id'   => $admin->id,
                'reviewed_at'           => now(),
            ]);
        });

        return $suggestion->fresh();
    }

    public function reject(CategorySuggestion $suggestion, User $admin, string $comment): CategorySuggestion
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

        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
