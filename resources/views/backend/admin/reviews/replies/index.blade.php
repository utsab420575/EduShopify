@extends('backend.layouts.admin')

@section('title', 'Review Replies')
@section('breadcrumb', 'Reviews & Moderation / Replies')

@section('body')

    <x-backend.page-header title="Review Replies" subtitle="Supplier replies to buyer reviews." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['published' => 'Published', 'hidden' => 'Hidden'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($replies->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-reply" title="No replies found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reply</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($replies as $reply)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $reply->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 max-w-sm truncate">{{ $reply->reply }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $reply->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$reply->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.reviews.show', $reply->review) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="View review"><i class="fa-regular fa-eye"></i></a>
                            @if($reply->status === 'published')
                                <button type="button" title="Hide" @click="$dispatch('open-modal-hide-{{ $reply->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-eye-slash"></i></button>
                            @else
                                <form method="POST" action="{{ route('admin.reviews.replies.publish', $reply) }}">
                                    @csrf
                                    <button type="submit" title="Publish" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$replies" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($replies as $reply)
        @if($reply->status === 'published')
            <x-backend.modal :id="'hide-'.$reply->id" title="Hide Reply">
                <form method="POST" action="{{ route('admin.reviews.replies.hide', $reply) }}">
                    @csrf
                    <x-backend.textarea name="reason" label="Reason" required />
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Hide</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
