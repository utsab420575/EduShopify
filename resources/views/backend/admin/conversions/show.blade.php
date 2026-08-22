@extends('backend.layouts.admin')

@section('title', $conversion->account?->display_name)
@section('breadcrumb', 'Users & Accounts / Conversions / ' . $conversion->account?->display_name)

@section('body')

    <x-backend.page-header :title="$conversion->account?->display_name" :subtitle="ucfirst($conversion->from_type) . ' &rarr; ' . ucfirst($conversion->to_type)">
        <x-slot:actions>
            <x-backend.status-badge :status="$conversion->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @if($conversion->status === 'pending')
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <form method="POST" action="{{ route('admin.conversions.approve', $conversion) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Approve</button>
            </form>
            <button @click="$dispatch('open-modal-revision')" class="text-sm font-medium px-4 py-2 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50">Request Revision</button>
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Proposed Organization Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="sm:col-span-2"><dt class="text-gray-500">Proposed Display Name</dt><dd class="font-medium text-gray-900">{{ $conversion->proposed_display_name }}</dd></div>
                    @foreach(($conversion->proposed_organization_data ?? []) as $key => $value)
                        @if(is_scalar($value))
                            <div><dt class="text-gray-500">{{ \Illuminate\Support\Str::headline($key) }}</dt><dd class="font-medium text-gray-900">{{ $value }}</dd></div>
                        @endif
                    @endforeach
                </dl>
            </x-backend.form-card>

            @if(!empty($conversion->submitted_documents))
                <x-backend.form-card title="Submitted Documents">
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($conversion->submitted_documents as $doc)
                            <li class="flex items-center justify-between px-5 py-3">
                                <span class="text-sm text-gray-800">{{ is_array($doc) ? ($doc['name'] ?? 'Document') : $doc }}</span>
                                @if(is_array($doc) && !empty($doc['path']))
                                    <a href="{{ asset('storage/'.$doc['path']) }}" target="_blank" class="text-xs" style="color:var(--theme-primary)">View file</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

            @if($conversion->review_comment)
                <x-backend.form-card title="Review Comment"><p class="text-sm text-gray-600">{{ $conversion->review_comment }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Request Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Account</dt><dd class="font-medium text-gray-900">
                        <a href="{{ route('admin.accounts.show', $conversion->account) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $conversion->account?->display_name }}</a>
                    </dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Submitted By</dt><dd class="font-medium text-gray-900">{{ $conversion->submittedBy?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Submitted</dt><dd class="font-medium text-gray-900">{{ $conversion->submitted_at?->format('d M Y') ?? '—' }}</dd></div>
                    @if($conversion->reviewed_at)
                        <div class="flex justify-between"><dt class="text-gray-500">Reviewed</dt><dd class="font-medium text-gray-900">{{ $conversion->reviewed_at->format('d M Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $conversion->reviewedBy?->name }}</dd></div>
                    @endif
                </dl>
            </x-backend.form-card>
        </div>
    </div>

    @if($conversion->status === 'pending')
        <x-backend.modal id="revision" title="Request Revision">
            <form method="POST" action="{{ route('admin.conversions.revision', $conversion) }}">
                @csrf
                <x-backend.textarea name="reason" label="What needs to change?" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send</button>
                </div>
            </form>
        </x-backend.modal>
        <x-backend.modal id="reject" title="Reject Conversion">
            <form method="POST" action="{{ route('admin.conversions.reject', $conversion) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for rejection" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
