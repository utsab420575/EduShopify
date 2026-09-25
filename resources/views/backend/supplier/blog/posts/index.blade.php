@extends('backend.layouts.supplier')

@section('title', 'Blog Posts')
@section('breadcrumb', 'Blog Posts')

@section('body')

    <x-backend.page-header title="Blog Posts" subtitle="Write and manage your articles. Submitted posts will be published on the platform blog once approved by an administrator.">
        <x-slot:actions>
            <a href="{{ route('supplier.blog.posts.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> New Post
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Tabs --}}
    <x-backend.tabs>
        <x-backend.tab :href="route('supplier.blog.posts.index', array_filter(['search' => $search]))" :active="$status === ''">
            All <span class="ml-1 text-xs text-gray-400">({{ $counts['all'] }})</span>
        </x-backend.tab>
        <x-backend.tab :href="route('supplier.blog.posts.index', array_filter(['status' => 'draft', 'search' => $search]))" :active="$status === 'draft'">
            Drafts <span class="ml-1 text-xs text-gray-400">({{ $counts['draft'] }})</span>
        </x-backend.tab>
        <x-backend.tab :href="route('supplier.blog.posts.index', array_filter(['status' => 'pending', 'search' => $search]))" :active="$status === 'pending'">
            Pending Approval <span class="ml-1 text-xs text-amber-500 font-semibold">({{ $counts['pending'] }})</span>
        </x-backend.tab>
        <x-backend.tab :href="route('supplier.blog.posts.index', array_filter(['status' => 'approved', 'search' => $search]))" :active="$status === 'approved'">
            Published <span class="ml-1 text-xs text-emerald-600 font-semibold">({{ $counts['approved'] }})</span>
        </x-backend.tab>
        <x-backend.tab :href="route('supplier.blog.posts.index', array_filter(['status' => 'rejected', 'search' => $search]))" :active="$status === 'rejected'">
            Rejected <span class="ml-1 text-xs text-red-500 font-semibold">({{ $counts['rejected'] }})</span>
        </x-backend.tab>
    </x-backend.tabs>

    {{-- Table --}}
    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search your posts by title..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($posts->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-newspaper" title="No blog posts found" description="Share educational insights, procurement guides, or company news with institutional buyers.">
                    <div class="mt-4">
                        <a href="{{ route('supplier.blog.posts.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> Write Your First Post
                        </a>
                    </div>
                </x-backend.empty-state>
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Engagement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($posts as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $post->coverImageUrl() }}"
                                 class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0" alt="">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate max-w-xs">{{ $post->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $post->reading_time_minutes }} min read</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->category?->name ?? 'Uncategorized' }}</td>
                    <td class="px-5 py-3.5">
                        @if($post->status === 'draft')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border bg-gray-100 text-gray-700 border-gray-200">
                                <i class="fa-solid fa-circle text-[6px]"></i> Draft
                            </span>
                        @elseif($post->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border bg-amber-50 text-amber-800 border-amber-200">
                                <i class="fa-solid fa-clock text-[10px]"></i> Pending Review
                            </span>
                        @elseif($post->status === 'approved')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border bg-green-50 text-green-700 border-green-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Published
                            </span>
                        @elseif($post->status === 'rejected')
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full border bg-red-50 text-red-700 border-red-200">
                                    <i class="fa-solid fa-xmark text-[10px]"></i> Rejected
                                </span>
                                @if($post->rejection_reason)
                                    <button type="button" @click="$dispatch('open-modal-reason-{{ $post->id }}')" title="View rejection reason" class="text-xs text-red-600 hover:text-red-800 underline ml-0.5 font-medium">
                                        Reason
                                    </button>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500">
                        <span title="Views"><i class="fa-regular fa-eye"></i> {{ $post->views_count }}</span>
                        <span class="ml-2" title="Likes"><i class="fa-regular fa-heart"></i> {{ $post->likes_count }}</span>
                        <span class="ml-2" title="Comments"><i class="fa-regular fa-comment"></i> {{ $post->comments_count }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500">
                        @if($post->status === 'approved' && $post->published_at)
                            <span class="text-gray-700 font-medium">{{ $post->published_at->format('d M Y') }}</span>
                            <span class="block text-[11px] text-gray-400">Published</span>
                        @else
                            <span>{{ $post->created_at->format('d M Y') }}</span>
                            <span class="block text-[11px] text-gray-400">Created</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-preview-post-{{ $post->id }}')"
                                    title="Preview Post"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($post->status === 'approved')
                                <a href="{{ route('v2.blogs.show', $post) }}" target="_blank" title="View live on website" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            @endif

                            <a href="{{ route('supplier.blog.posts.edit', $post) }}" title="Edit Post" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>

                            <form method="POST" action="{{ route('supplier.blog.posts.destroy', $post) }}" onsubmit="return confirmSwal(this, 'Delete this post?', '{{ addslashes($post->title) }} will be permanently removed.', 'warning', 'Yes, delete')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete Post" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
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

    {{-- Modals for Preview & Rejection Reasons --}}
    @foreach($posts as $post)
        {{-- Preview Modal --}}
        <x-backend.modal :id="'preview-post-'.$post->id" :title="$post->title" width="max-w-3xl">
            <div class="space-y-4 max-h-[75vh] overflow-y-auto pr-1">
                @if($post->cover_image)
                    <img src="{{ $post->coverImageUrl() }}" alt="" class="w-full h-56 object-cover rounded-xl border border-gray-200">
                @endif

                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 pb-3 border-b border-gray-100">
                    <span><i class="fa-solid fa-folder text-gray-400 mr-1"></i> {{ $post->category?->name ?? 'Uncategorized' }}</span>
                    <span><i class="fa-regular fa-clock text-gray-400 mr-1"></i> {{ $post->reading_time_minutes }} min read</span>
                    <span><i class="fa-regular fa-calendar text-gray-400 mr-1"></i> {{ $post->created_at->format('d M Y') }}</span>
                    @if($post->status === 'approved')
                        <span class="text-emerald-600 font-semibold"><i class="fa-solid fa-check mr-1"></i> Approved &amp; Published</span>
                    @elseif($post->status === 'pending')
                        <span class="text-amber-600 font-semibold"><i class="fa-solid fa-clock mr-1"></i> Pending Administrator Approval</span>
                    @elseif($post->status === 'rejected')
                        <span class="text-red-600 font-semibold"><i class="fa-solid fa-xmark mr-1"></i> Rejected</span>
                    @endif
                </div>

                @if($post->excerpt)
                    <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700 italic border-l-4 border-gray-300">
                        {{ $post->excerpt }}
                    </div>
                @endif

                <div class="prose prose-sm max-w-none text-gray-800 leading-relaxed pt-2">
                    {!! $post->content !!}
                </div>

                @if($post->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5 pt-3 border-t border-gray-100">
                        @foreach($post->tags as $tag)
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between mt-6 pt-3 border-t border-gray-100">
                <a href="{{ route('supplier.blog.posts.edit', $post) }}" class="btn-primary text-xs font-semibold px-3.5 py-2 rounded-lg">
                    <i class="fa-regular fa-pen-to-square mr-1"></i> Edit Post
                </a>
                <button type="button" @click="open = false" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Close
                </button>
            </div>
        </x-backend.modal>

        {{-- Rejection Reason Modal --}}
        @if($post->status === 'rejected' && $post->rejection_reason)
            <x-backend.modal :id="'reason-'.$post->id" title="Administrator Rejection Feedback" width="max-w-lg">
                <div class="space-y-4">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-900 leading-relaxed">
                        <div class="flex items-center gap-2 font-semibold text-red-800 mb-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                            Why was this post rejected?
                        </div>
                        <p class="whitespace-pre-line">{{ $post->rejection_reason }}</p>
                    </div>

                    <p class="text-xs text-gray-500">
                        You can update your article to address the feedback and resubmit it for administrator review anytime.
                    </p>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Close
                        </button>
                        <a href="{{ route('supplier.blog.posts.edit', $post) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit and Resubmit
                        </a>
                    </div>
                </div>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
