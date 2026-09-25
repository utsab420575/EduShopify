<?php

namespace App\Http\Requests\Backend\Supplier\Blog;

use Illuminate\Foundation\Http\FormRequest;

class SupplierBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:10240',
            'status' => 'required|in:draft,pending',
            'tags' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a title for your blog post.',
            'content.required' => 'Please write the blog post content.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}
