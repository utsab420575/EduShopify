@extends('backend.layouts.supplier')

@section('title', 'New Blog Post')
@section('breadcrumb', 'Blog Posts / New Post')

@section('body')

    <x-backend.page-header title="Write a New Blog Post" subtitle="Create educational articles and industry insights. Once approved by admins, your post will be featured on the platform blog." />

    <form method="POST" action="{{ route('supplier.blog.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('backend.supplier.blog.posts._form', ['post' => $post, 'categories' => $categories, 'tagNames' => $tagNames])

        <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
            <a href="{{ route('supplier.blog.posts.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <div class="flex items-center gap-2.5">
                <button type="submit" name="status" value="draft" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fa-regular fa-bookmark mr-1.5 text-gray-400"></i> Save as Draft
                </button>
                <button type="submit" name="status" value="pending" class="btn-primary text-sm font-semibold px-5 py-2 rounded-lg shadow-sm">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Submit for Approval
                </button>
            </div>
        </div>
    </form>

@endsection
