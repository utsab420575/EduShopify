@extends('backend.layouts.admin')

@section('title', 'Review Report')
@section('breadcrumb', 'Reviews & Moderation / Reports / Detail')

@section('body')

    <x-backend.page-header title="Review Report" :subtitle="'Reported by ' . $report->reportedByAccount?->display_name">
        <x-slot:actions>
            <x-backend.status-badge :status="$report->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @if($report->status === 'pending')
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <button @click="$dispatch('open-modal-action')" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Take Action</button>
            <form method="POST" action="{{ route('admin.reviews.reports.dismiss', $report) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Dismiss</button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Report Details">
                <dl class="text-sm space-y-3">
                    <div><dt class="text-gray-500">Reason</dt><dd class="font-medium text-gray-900">{{ $report->reason }}</dd></div>
                    @if($report->details)
                        <div><dt class="text-gray-500">Details</dt><dd class="text-gray-700">{{ $report->details }}</dd></div>
                    @endif
                </dl>
            </x-backend.form-card>

            @if($report->review)
                <x-backend.form-card title="Reported Review">
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $report->review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600">{{ $report->review->comment }}</p>
                    <a href="{{ route('admin.reviews.show', $report->review) }}" class="text-sm font-medium mt-3 inline-block" style="color:var(--theme-primary)">View full review &rarr;</a>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Reported By">
                <p class="text-sm font-medium text-gray-900">{{ $report->reportedByAccount?->display_name }}</p>
                <p class="text-xs text-gray-400">{{ $report->reportedBy?->name }}</p>
            </x-backend.form-card>

            @if($report->status !== 'pending')
                <x-backend.form-card title="Resolution">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-gray-500">Action</dt><dd class="font-medium text-gray-900">{{ ucfirst($report->review_action ?? '—') }}</dd></div>
                        <div><dt class="text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $report->reviewedBy?->name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Reviewed At</dt><dd class="font-medium text-gray-900">{{ $report->reviewed_at?->format('d M Y') ?? '—' }}</dd></div>
                    </dl>
                </x-backend.form-card>
            @endif
        </div>
    </div>

    @if($report->status === 'pending')
        <x-backend.modal id="action" title="Take Action on Review">
            <form method="POST" action="{{ route('admin.reviews.reports.action-taken', $report) }}">
                @csrf
                <x-backend.select name="review_action" label="Action" required :options="['hidden' => 'Hide the review', 'rejected' => 'Reject the review', 'warned' => 'Warn the author (no change to review)']" />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Submit</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
