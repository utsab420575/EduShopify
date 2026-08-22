@extends('backend.layouts.supplier')

@section('title', 'Ownership')
@section('breadcrumb', 'Organization / Ownership')

@section('body')

    <x-backend.page-header title="Ownership" subtitle="Manage account ownership and transfers." />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-backend.form-card title="Current Owner(s)">
            <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                @foreach($owners as $owner)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $owner->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $owner->user?->email }}</p>
                        </div>
                        @if($owner->is_primary_owner)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Primary Owner</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-backend.form-card>

        <x-backend.form-card title="Transfer Ownership">
            <form method="POST" action="{{ route('supplier.ownership.transfer') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Select New Primary Owner <span class="text-red-500">*</span></label>
                    <select name="target_user_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                        <option value="">Select team member</option>
                        @foreach($members as $member)
                            <option value="{{ $member->user_id }}">{{ $member->user?->name }} ({{ $member->user?->email }})</option>
                        @endforeach
                    </select>
                </div>
                <x-backend.textarea name="reason" label="Reason (optional)" />
                <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-right-left"></i> Initiate Transfer
                </button>
            </form>
        </x-backend.form-card>
    </div>

    <x-backend.form-card title="Transfer History">
        @if($transfers->isEmpty())
            <p class="text-sm text-gray-400">No ownership transfers recorded.</p>
        @else
            <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                @foreach($transfers as $transfer)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm text-gray-800">{{ $transfer->fromUser?->name }} &rarr; {{ $transfer->toUser?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $transfer->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-backend.status-badge :status="$transfer->status" />
                            @if($transfer->status === 'pending' && $transfer->to_user_id === auth()->id())
                                <form method="POST" action="{{ route('supplier.ownership.accept', $transfer) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-green-700">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('supplier.ownership.reject', $transfer) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-red-600">Decline</button>
                                </form>
                            @elseif($transfer->status === 'pending')
                                <form method="POST" action="{{ route('supplier.ownership.cancel', $transfer) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-red-600">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-backend.form-card>

@endsection
