@extends('backend.layouts.supplier')

@section('title', 'Inquiry ' . $inquiry->inquiry_number)
@section('breadcrumb', 'Communication / Contact Inquiries / ' . $inquiry->inquiry_number)

@section('body')

    <x-backend.page-header
        title="Inquiry {{ $inquiry->inquiry_number }}"
        subtitle="Received {{ $inquiry->created_at->format('d M Y, h:i A') }}" />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            {{-- Message content --}}
            <x-backend.form-card title="Inquiry Message">
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-gray-400 block">Subject</span>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $inquiry->subject }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $inquiry->message }}</p>
                    </div>
                </div>
            </x-backend.form-card>

            {{-- Listing context --}}
            @if($inquiry->listing)
                <x-backend.form-card title="Related Listing">
                    <p class="text-sm font-medium text-gray-900">{{ $inquiry->listing->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $inquiry->listing->listing_number }}</p>
                </x-backend.form-card>
            @endif

        </div>

        <div class="xl:col-span-4 space-y-6">

            {{-- Contact details --}}
            <x-backend.form-card title="Sender Details">
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">Name</span>
                        <p class="font-semibold text-gray-900">{{ $inquiry->name }}</p>
                    </div>
                    @if($inquiry->organization)
                        <div>
                            <span class="text-xs text-gray-400 block">Organization</span>
                            <p class="text-gray-700">{{ $inquiry->organization }}</p>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-400 block">Email</span>
                        <a href="mailto:{{ $inquiry->email }}" class="text-indigo-600 hover:underline text-xs">{{ $inquiry->email }}</a>
                    </div>
                    @if($inquiry->phone)
                        <div>
                            <span class="text-xs text-gray-400 block">Phone</span>
                            <p class="text-gray-700 text-xs">{{ $inquiry->phone }}</p>
                        </div>
                    @endif
                </div>
            </x-backend.form-card>

            {{-- Status & actions --}}
            <x-backend.form-card title="Actions">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xs text-gray-500">Status:</span>
                    <x-backend.status-badge :status="$inquiry->status" />
                </div>
                <div class="space-y-2">
                    @if(in_array($inquiry->status, ['new', 'read']))
                        <form method="POST" action="{{ route('supplier.contact-inquiries.mark-replied', $inquiry) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full btn-primary text-xs font-semibold px-4 py-2.5 rounded-lg flex items-center justify-center gap-2">
                                <i class="fa-solid fa-reply"></i> Mark as Replied
                            </button>
                        </form>
                    @endif
                    @if($inquiry->status !== 'closed')
                        <form method="POST" action="{{ route('supplier.contact-inquiries.close', $inquiry) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-xs font-semibold px-4 py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-xmark"></i> Close Inquiry
                            </button>
                        </form>
                    @endif
                    <a href="mailto:{{ $inquiry->email }}"
                       class="w-full text-xs font-semibold px-4 py-2.5 rounded-lg border border-indigo-200 text-indigo-700 hover:bg-indigo-50 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-envelope"></i> Reply via Email
                    </a>
                </div>
            </x-backend.form-card>

        </div>
    </div>

@endsection
