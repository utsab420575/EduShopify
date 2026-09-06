<?php

namespace App\Http\Controllers\Backend\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Blog\StoreBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Support\Str;

/**
 * Quick-add only — a full Blog Categories CRUD page wasn't requested, but
 * the post form needs some way to grow the category list without leaving
 * it, so this is a tiny AJAX endpoint used from a "+ New Category" modal
 * on the Create/Edit Post pages instead.
 */
class BlogCategoryController extends Controller
{
    public function store(StoreBlogCategoryRequest $request)
    {
        $this->authorize('platform.blog.manage');

        $category = BlogCategory::create([
            'name' => $request->string('name'),
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => true,
        ]);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (BlogCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
