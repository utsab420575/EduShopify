@extends('backend.layouts.admin')

@section('title', 'Blog Approval')
@section('breadcrumb', 'Blog / Blog Approval')

@section('body')

    <x-backend.page-header title="Blog Approval" subtitle="Review blog posts submitted for publication." />

    <x-backend.tabs>
        <x-backend.tab :href="route('admin.blog.approval.index', array_filter(['search' => $search]))" :active="$status === ''">
            All <span class="ml-1 text-xs text-gray-400">({{ $counts['all'] }})</span>
        </x-backend.tab>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <x-backend.tab :href="route('admin.blog.approval.index', array_filter(['status' => $value, 'search' => $search]))" :active="$status === $value">
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
            <x-slot:empty><x-backend.empty-state icon="fa-newspaper" title="No blog posts found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reviewed By</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($posts as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900 max-w-xs truncate">{{ $post->title }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->account?->display_name ?? $post->authorUser?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $post->approvedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$post->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-view-blog-post-{{ $post->id }}')"
                                    title="View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($post->status === 'pending')
                                <form method="POST" action="{{ route('admin.blog.approval.approve', $post) }}" onsubmit="return confirmSwal(this, 'Approve Post?', 'Publish {{ addslashes($post->title) }}?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        title="Reject"
                                        @click="$dispatch('open-modal-reject-blog-post-{{ $post->id }}')"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.blog.approval.undo', $post) }}" onsubmit="return confirmSwal(this, 'Undo Decision?', 'Revert {{ addslashes($post->title) }} back to Pending?', 'question', 'Yes, Undo')">
                                    @csrf
                                    <button type="submit" title="Undo Decision (Revert to Pending)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$posts" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($posts as $post)
        <x-backend.modal :id="'view-blog-post-'.$post->id" :title="$post->title" width="max-w-2xl">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-500">Author Account</dt><dd class="font-medium text-gray-900">{{ $post->account?->display_name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ $post->category?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$post->status" /></dd></div>
                <div><dt class="text-xs text-gray-500">Submitted</dt><dd class="font-medium text-gray-900">{{ $post->created_at->format('d M Y, h:i A') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $post->approvedBy?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Reading Time</dt><dd class="font-medium text-gray-900">{{ $post->reading_time_minutes }} min</dd></div>
                @if($post->cover_image)
                    <div class="sm:col-span-2">
                        <img src="{{ str_starts_with($post->cover_image, 'http') || str_starts_with($post->cover_image, '/') ? $post->cover_image : \Illuminate\Support\Facades\Storage::url($post->cover_image) }}" class="w-full h-48 object-cover rounded-lg border border-gray-200">
                    </div>
                @endif
                @if($post->excerpt)
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Excerpt</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $post->excerpt }}</dd></div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500 mb-1">Content</dt>
                    <dd class="text-xs text-gray-700 prose prose-sm max-w-none max-h-64 overflow-y-auto border border-gray-100 rounded-lg p-3 bg-gray-50/50">{!! $post->content !!}</dd>
                </div>
                @if($post->status === 'rejected' && $post->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-xs text-red-500 font-semibold">Rejection Reason</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $post->rejection_reason }}</dd></div>
                @endif
            </dl>
        </x-backend.modal>

        @if($post->status === 'pending')
            <x-backend.modal :id="'reject-blog-post-'.$post->id" title="Reject Blog Post">
                <form method="POST" action="{{ route('admin.blog.approval.reject', $post) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Reason for Rejection" placeholder="State why this post is being rejected..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Post</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
