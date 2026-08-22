@extends('backend.layouts.buyer')

@section('title', 'My Reviews')
@section('breadcrumb', 'Reviews')

@section('body')

    <x-backend.page-header title="My Reviews" subtitle="Reviews you've written for suppliers." />

    @if($reviews->isEmpty())
        <x-backend.empty-state icon="fa-star" title="No reviews yet" description="After a quotation experience or completed purchase, you can leave a review from that page." />
    @else
        <div class="space-y-4">
            @foreach($reviews as $review)
                <x-backend.form-card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $review->supplierAccount?->supplierProfile?->display_name }}</p>
                            <div class="flex items-center gap-0.5 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                            @if($review->title)
                                <p class="text-sm font-semibold text-gray-800 mt-2">{{ $review->title }}</p>
                            @endif
                            @if($review->comment)
                                <p class="text-sm text-gray-600 mt-1">{{ $review->comment }}</p>
                            @endif
                            @if($review->reply)
                                <div class="mt-3 pl-3 border-l-2 border-gray-100">
                                    <p class="text-xs font-semibold text-gray-500">Supplier reply</p>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ $review->reply->reply }}</p>
                                </div>
                            @endif
                        </div>
                        <x-backend.status-badge :status="$review->status" />
                    </div>
                </x-backend.form-card>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$reviews" />
        </div>
    @endif

@endsection
