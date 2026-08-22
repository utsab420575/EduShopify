@extends('backend.layouts.admin')

@section('title', $account->display_name)
@section('breadcrumb', 'Users & Accounts / Closures / ' . $account->display_name)

@section('body')

    <x-backend.page-header :title="$account->display_name" subtitle="Closure request review">
        <x-slot:actions>
            <a href="{{ route('admin.accounts.show', $account) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">View Account</a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Dependency Report">
                @if($blockers->isEmpty())
                    <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>No blocking dependencies. This account can be safely closed.</span>
                    </div>
                @else
                    <ul class="space-y-2">
                        @foreach($blockers as $blocker)
                            <li class="flex items-center gap-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Account has {{ $blocker }}.</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Request Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Owner</dt><dd class="font-medium text-gray-900">{{ $account->primaryOwner?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Requested</dt><dd class="font-medium text-gray-900">{{ $account->deletion_requested_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </x-backend.form-card>

            <x-backend.form-card title="Actions">
                <div class="space-y-2">
                    <form method="POST" action="{{ route('admin.closures.finalize', $account) }}" @if($blockers->isNotEmpty()) onsubmit="return false;" @endif>
                        @csrf
                        <button type="submit" @disabled($blockers->isNotEmpty()) class="w-full text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed">Finalize Closure</button>
                    </form>
                    <button type="button" @click="$dispatch('open-modal-hold')" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Hold &amp; Reactivate</button>
                </div>
            </x-backend.form-card>
        </div>
    </div>

    <x-backend.modal id="hold" title="Hold Closure &amp; Reactivate Account">
        <form method="POST" action="{{ route('admin.closures.hold', $account) }}">
            @csrf
            <x-backend.textarea name="reason" label="Reason" required />
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Hold &amp; Reactivate</button>
            </div>
        </form>
    </x-backend.modal>

@endsection
