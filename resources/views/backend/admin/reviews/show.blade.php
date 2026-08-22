@extends('backend.layouts.admin')

@section('title', 'Review')
@section('breadcrumb', 'Reviews & Moderation / Reviews / Detail')

@section('body')

    <x-backend.page-header :title="$review->title ?: 'Review'" :subtitle="$review->buyerAccount?->display_name . ' &rarr; ' . ($review->supplierAccount?->supplierProfile?->display_name ?? '')">
        <x-slot:actions>
            <x-backend.status-badge :status="$review->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if(in_array($review->status, ['pending', 'flagged']))
            <form method="POST" action="{{ route('admin.reviews.publish', $review) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Publish</button>
            </form>
        @endif
        @if($review->status === 'pending')
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        @endif
        @if($review->status === 'published')
            <button @click="$dispatch('open-modal-hide')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Hide</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Review Content">
                <div class="mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                </div>
                <p class="text-sm text-gray-600">{{ $review->comment }}</p>
                <p class="text-xs text-gray-400 mt-2">Context: {{ str_replace('_', ' ', $review->review_context) }}</p>
            </x-backend.form-card>

            @if($review->reply)
                <x-backend.form-card title="Supplier Reply">
                    <p class="text-sm text-gray-600">{{ $review->reply->reply }}</p>
                    <div class="mt-2"><x-backend.status-badge :status="$review->reply->status" /></div>
                </x-backend.form-card>
            @endif

            @if($review->reports->isNotEmpty())
                <x-backend.form-card title="Reports">
                    <ul class="space-y-3">
                        @foreach($review->reports as $report)
                            <li class="text-sm">
                                <div class="flex justify-between"><span class="text-gray-700">{{ $report->reportedByAccount?->display_name }} &mdash; {{ $report->reason }}</span><x-backend.status-badge :status="$report->status" /></div>
                                <a href="{{ route('admin.reviews.reports.show', $report) }}" class="text-xs" style="color:var(--theme-primary)">View report &rarr;</a>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

            @if($review->moderation_reason)
                <x-backend.form-card title="Moderation Reason"><p class="text-sm text-gray-600">{{ $review->moderation_reason }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Parties">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-500">Buyer</dt><dd class="font-medium text-gray-900">{{ $review->buyerAccount?->display_name }}</dd></div>
                    <div><dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.suppliers.show', $review->supplierAccount) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $review->supplierAccount?->supplierProfile?->display_name }}</a></dd></div>
                </dl>
            </x-backend.form-card>
        </div>
    </div>

    @if($review->status === 'pending')
        <x-backend.modal id="reject" title="Reject Review">
            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                </div>
            </form>
        </x-backend.modal>
    @endif
    @if($review->status === 'published')
        <x-backend.modal id="hide" title="Hide Review">
            <form method="POST" action="{{ route('admin.reviews.hide', $review) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Hide</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
