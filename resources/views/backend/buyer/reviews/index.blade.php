@extends('backend.layouts.buyer')

@section('title', 'My Reviews')
@section('breadcrumb', 'Reviews')

@section('body')

    <x-backend.page-header title="My Reviews" subtitle="Reviews you've written for suppliers and products." />

    @if($reviews->isEmpty())
        <x-backend.empty-state icon="fa-star" title="No reviews yet" description="After a quotation experience or completed purchase, you can leave a review from that page." />
    @else
        <div class="space-y-4">
            @foreach($reviews as $review)
                <x-backend.form-card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide {{ $review->review_type === 'product' ? 'text-purple-600' : 'text-indigo-600' }}">
                                {{ $review->review_type === 'product' ? 'Product' : 'Supplier' }}
                            </p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">
                                {{ $review->review_type === 'product' ? ($review->listing?->name ?? 'Product') : $review->supplierAccount?->supplierProfile?->display_name }}
                            </p>
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
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <x-backend.status-badge :status="$review->status" />
                            @if($review->review_type === 'product')
                                <button @click="$dispatch('open-modal-edit-review-{{ $review->id }}')" class="text-xs font-medium hover:underline" style="color:var(--theme-primary)">Edit</button>
                            @endif
                        </div>
                    </div>
                </x-backend.form-card>

                @if($review->review_type === 'product')
                    <x-backend.modal id="edit-review-{{ $review->id }}" title="Edit Product Review">
                        <form method="POST" action="{{ route('buyer.reviews.update-product', $review) }}" x-data="{ rating: {{ $review->rating }} }">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rating</label>
                                <div class="flex items-center gap-1">
                                    <template x-for="star in [1,2,3,4,5]" :key="star">
                                        <button type="button" @click="rating = star" class="text-xl" :class="star <= rating ? 'text-amber-400' : 'text-gray-200'">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </template>
                                    <input type="hidden" name="rating" :value="rating">
                                </div>
                            </div>
                            <x-backend.input name="title" label="Title (optional)" :value="$review->title" />
                            <div class="mt-4">
                                <x-backend.textarea name="comment" label="Comment (optional)" :value="$review->comment" />
                            </div>
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                            </div>
                        </form>
                    </x-backend.modal>
                @endif
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$reviews" />
        </div>
    @endif

@endsection
