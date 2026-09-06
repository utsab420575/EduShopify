@extends('backend.layouts.admin')

@section('title', 'Blog Posts')
@section('breadcrumb', 'Blog / Blog Create')

@section('body')

    <x-backend.page-header title="Blog Posts" subtitle="Write, edit, and manage every blog post on the platform.">
        <x-slot:actions>
            <a href="{{ route('admin.blog.posts.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Post</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.tabs>
        <x-backend.tab :href="route('admin.blog.posts.index', array_filter(['search' => $search]))" :active="$status === ''">
            All <span class="ml-1 text-xs text-gray-400">({{ $counts['all'] }})</span>
        </x-backend.tab>
        @foreach(['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Published', 'rejected' => 'Rejected'] as $value => $label)
            <x-backend.tab :href="route('admin.blog.posts.index', array_filter(['status' => $value, 'search' => $search]))" :active="$status === $value">
                {{ $label }} <span class="ml-1 text-xs text-gray-400">({{ $counts[$value] }})</span>
            </x-backend.tab>
        @endforeach
    </x-backend.tabs>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search posts..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($posts->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-newspaper" title="No blog posts found" description="Click New Post to write your first article." /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stats</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($posts as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $post->cover_image ? (str_starts_with($post->cover_image, 'http') || str_starts_with($post->cover_image, '/') ? $post->cover_image : \Illuminate\Support\Facades\Storage::url($post->cover_image)) : asset('images/herosection.png') }}"
                                 class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0" alt="">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate max-w-xs">{{ $post->title }}</p>
                                <p class="text-xs text-gray-400">{{ $post->created_at->format('d M Y') }}
                                    @if($post->featured)<span class="ml-1.5 text-[10px] font-semibold text-amber-600"><i class="fa-solid fa-star"></i> Featured</span>@endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->category?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->authorUser?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$post->status" /></td>
                    <td class="px-5 py-3.5 text-xs text-gray-500">
                        <span title="Views"><i class="fa-regular fa-eye"></i> {{ $post->views_count }}</span>
                        <span class="ml-2" title="Likes"><i class="fa-regular fa-heart"></i> {{ $post->likes_count }}</span>
                        <span class="ml-2" title="Comments"><i class="fa-regular fa-comment"></i> {{ $post->comments_count }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.blog.posts.edit', $post) }}" title="Edit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}" onsubmit="return confirmSwal(this, 'Delete this post?', '{{ addslashes($post->title) }} will be permanently removed.', 'warning', 'Yes, delete')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$posts" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
