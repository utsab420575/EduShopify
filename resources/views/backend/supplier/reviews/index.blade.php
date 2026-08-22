@extends('backend.layouts.supplier')

@section('title', 'Client Reviews')
@section('breadcrumb', 'Reviews / Received Reviews')

@section('body')

    <x-backend.page-header title="Customer Reviews" subtitle="Feedback received from institutional buyers on completed orders and quotation experiences." />

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($reviews->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-star" title="No reviews yet" description="Reviews left by buyers following quotation interactions or completed purchase orders will appear here." />
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($reviews as $rev)
                    <div class="p-6 space-y-4 hover:bg-gray-50/50">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5 text-amber-400 text-sm mb-1">
                                    @for($s = 1; $s <= 5; $s++)
                                        <i class="fa-solid fa-star {{ $s <= $rev->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                    <span class="text-xs font-bold text-gray-800 ml-1.5">{{ $rev->rating }}.0</span>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900">{{ $rev->title ?? 'Review' }}</h4>
                            </div>

                            <div class="text-right text-xs text-gray-400">
                                <span>{{ $rev->created_at->format('d M Y') }}</span> &middot;
                                <span class="font-medium text-gray-600">{{ $rev->buyerAccount?->buyerProfile?->organization_name ?? $rev->buyerAccount?->display_name }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-700 leading-relaxed">{{ $rev->comment }}</p>

                        {{-- Existing reply --}}
                        @if($rev->reply)
                            <div class="bg-indigo-50/40 border-l-2 border-indigo-500 p-3 rounded-r-lg text-xs space-y-1">
                                <span class="font-bold text-indigo-900 block">Your Reply:</span>
                                <p class="text-gray-700">{{ $rev->reply->reply }}</p>
                            </div>
                        @else
                            {{-- Reply form accordion/box --}}
                            <div x-data="{ showReply: false }" class="pt-2">
                                <button @click="showReply = !showReply" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                    <i class="fa-solid fa-reply"></i> <span x-text="showReply ? 'Cancel' : 'Reply to this review'"></span>
                                </button>
                                <form x-show="showReply" method="POST" action="{{ route('supplier.reviews.reply', $rev) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <x-backend.textarea name="reply" placeholder="Write a professional response to this buyer..." required />
                                    <button type="submit" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg">
                                        Publish Response
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if($reviews->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $reviews->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
