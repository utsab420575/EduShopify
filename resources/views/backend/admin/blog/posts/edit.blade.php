@extends('backend.layouts.admin')

@section('title', 'Edit Blog Post')
@section('breadcrumb', 'Blog / Blog Create / Edit')

@section('body')

    <x-backend.page-header title="Edit Blog Post" :subtitle="$post->title" />

    <form method="POST" action="{{ route('admin.blog.posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('backend.admin.blog.posts._form', ['post' => $post, 'categories' => $categories, 'tagNames' => $tagNames])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.blog.posts.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Update Post</button>
        </div>
    </form>

@endsection
